<?php

namespace App\Http\Controllers;

use App\Models\DateBlocker;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class DateBlockerController extends Controller
{
    public function index(Request $request)
    {
        $dateBlockers = DateBlocker::with('roomType')
            ->where('package_id', $request->package_id)
            ->orderBy('start_date', 'desc') // Order by start_date descending
            ->paginate(10)
            ->withQueryString();

        return $request->wantsJson()
            ? response()->json($dateBlockers)
            : Inertia::render('Packages/Show', ['dateBlockers' => $dateBlockers]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'room_type_id' => 'required',
        ], [
            'end_date.after_or_equal' => 'End date must be the same as or after the start date.',
        ]);

        // Handle "all" room types - create blockers for each room type in the package
        if ($validated['room_type_id'] === 'all') {
            return $this->storeForAllRoomTypes($request, $validated);
        }

        // Validate room_type_id exists for single room type
        $request->validate([
            'room_type_id' => 'exists:room_types,id',
        ]);

        if (DateBlocker::hasOverlappingDates($validated['start_date'], $validated['end_date'], $validated['package_id'], null, $validated['room_type_id'])) {
            $blocker = DateBlocker::where('package_id', $validated['package_id'])
                ->where('room_type_id', $validated['room_type_id'])
                ->where('start_date', '<=', $validated['end_date'])
                ->where('end_date', '>=', $validated['start_date'])
                ->first();

            return $this->respondWithOverlap($request, $blocker);
        }

        try {
            $dateBlocker = DateBlocker::create($validated);

            return $request->wantsJson()
                ? response()->json(['message' => 'Date blocker created successfully', 'dateBlocker' => $dateBlocker])
                : back()->with('success', 'Date blocker created successfully');
        } catch (\Exception $e) {
            return $this->respondWithException($request, 'creating', $e, $request->all());
        }
    }

    private function storeForAllRoomTypes(Request $request, array $validated)
    {
        $roomTypes = RoomType::where('package_id', $validated['package_id'])->get();

        if ($roomTypes->isEmpty()) {
            return $request->wantsJson()
                ? response()->json(['message' => 'No room types found for this package'], 422)
                : back()->withErrors(['room_type_id' => 'No room types found for this package']);
        }

        $created = 0;
        $skipped = 0;
        $skippedRoomTypes = [];

        try {
            foreach ($roomTypes as $roomType) {
                // Check for overlapping dates for this room type
                if (DateBlocker::hasOverlappingDates($validated['start_date'], $validated['end_date'], $validated['package_id'], null, $roomType->id)) {
                    $skipped++;
                    $skippedRoomTypes[] = $roomType->name;
                    continue;
                }

                DateBlocker::create([
                    'package_id' => $validated['package_id'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'room_type_id' => $roomType->id,
                ]);
                $created++;
            }

            $message = "{$created} date blocker(s) created successfully";
            if ($skipped > 0) {
                $message .= ". {$skipped} room type(s) skipped due to overlapping dates: " . implode(', ', $skippedRoomTypes);
            }

            return $request->wantsJson()
                ? response()->json(['message' => $message, 'created' => $created, 'skipped' => $skipped])
                : back()->with('success', $message);
        } catch (\Exception $e) {
            return $this->respondWithException($request, 'creating', $e, $request->all());
        }
    }

    public function update(Request $request, DateBlocker $dateBlocker)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ], [
            'end_date.after_or_equal' => 'End date must be the same as or after the start date.',
        ]);

        if (DateBlocker::hasOverlappingDates($validated['start_date'], $validated['end_date'], $dateBlocker->package_id, $dateBlocker->id)) {
            $blocker = DateBlocker::where('package_id', $dateBlocker->package_id)
                ->where('id', '!=', $dateBlocker->id)
                ->where('start_date', '<=', $validated['end_date'])
                ->where('end_date', '>=', $validated['start_date'])
                ->first();

            return $this->respondWithOverlap($request, $blocker);
        }

        try {
            $dateBlocker->update($validated);

            return $request->wantsJson()
                ? response()->json(['message' => 'Date blocker updated successfully', 'dateBlocker' => $dateBlocker])
                : back()->with('success', 'Date blocker updated successfully');
        } catch (\Exception $e) {
            return $this->respondWithException($request, 'updating', $e, $request->all());
        }
    }

    public function destroy(DateBlocker $dateBlocker)
    {
        try {
            $dateBlocker->delete();

            return request()->wantsJson()
                ? response()->json(['message' => 'Date blocker deleted successfully'])
                : back()->with('success', 'Date blocker deleted successfully');
        } catch (\Exception $e) {
            return $this->respondWithException(request(), 'deleting', $e, ['date_blocker_id' => $dateBlocker->id]);
        }
    }

    public function destroyBulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:date_blockers,id',
            'package_id' => 'required|exists:packages,id'
        ]);

        try {
            $deletedCount = DateBlocker::whereIn('id', $validated['ids'])
                ->where('package_id', $validated['package_id'])
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} date blocker(s) deleted successfully",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return $this->respondWithException($request, 'bulk deleting', $e, ['ids' => $validated['ids']]);
        }
    }

    // 🧩 Shared Logic

    private function respondWithOverlap(Request $request, DateBlocker $blocker)
    {
        $range = Carbon::parse($blocker->start_date)->format('d-m-Y') . ' to ' . Carbon::parse($blocker->end_date)->format('d-m-Y');
        $room = $blocker->roomType->name ?? 'N/A';
        $message = "This date range overlaps with an existing date blocker for this package" .
            (isset($blocker->room_type_id) ? " and room type in the date range of $range for room type $room" : " in the date range of $range");

        return $request->wantsJson()
            ? response()->json(['message' => $message], 422)
            : back()->withErrors(['date_range' => $message]);
    }

    private function respondWithException(Request $request, $action, \Exception $e, array $context = [])
    {
        Log::error("Date blocker {$action} error", array_merge([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], $context));

        $message = "Error {$action} date blocker: " . $e->getMessage();

        return $request->wantsJson()
            ? response()->json(['message' => $message], 500)
            : back()->withErrors(['error' => $message]);
    }
}
