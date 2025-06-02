<?php

namespace App\Http\Controllers;

use App\Models\DateBlocker;
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
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($dateBlockers);
        }

        return Inertia::render('Packages/Show', [
            'dateBlockers' => $dateBlockers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'room_type_id' => 'required|exists:room_types,id',
        ]);

        // Check for overlapping dates
        if (DateBlocker::hasOverlappingDates($validated['start_date'], $validated['end_date'], $validated['package_id'], null, $validated['room_type_id'])) {
            $dateBlocker = DateBlocker::where('package_id', $validated['package_id'])
                ->where('room_type_id', $validated['room_type_id'])
                ->where('start_date', '<=', $validated['end_date'])
                ->where('end_date', '>=', $validated['start_date'])
                ->first();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'This date range overlaps with an existing date blocker for this package and room type in the date range of ' . 
                        Carbon::parse($dateBlocker->start_date)->format('d-m-Y') . ' to ' . 
                        Carbon::parse($dateBlocker->end_date)->format('d-m-Y') . ' for room type ' . $dateBlocker->roomType->name
                ], 422);
            }

            return back()->withErrors([
                'date_range' => 'This date range overlaps with an existing date blocker for this package and room type in the date range of ' . 
                    Carbon::parse($dateBlocker->start_date)->format('d-m-Y') . ' to ' . 
                    Carbon::parse($dateBlocker->end_date)->format('d-m-Y') . ' for room type ' . $dateBlocker->roomType->name
            ]);
        }

        try {
            $dateBlocker = DateBlocker::create($validated);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Date blocker created successfully',
                    'dateBlocker' => $dateBlocker
                ]);
            }

            return back()->with('success', 'Date blocker created successfully');
        } catch (\Exception $e) {
            Log::error('Date blocker creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Error creating date blocker: ' . $e->getMessage()], 500);
            }

            return back()->withErrors(['error' => 'Error creating date blocker: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, DateBlocker $dateBlocker)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Check for overlapping dates, excluding current date blocker
        if (DateBlocker::hasOverlappingDates($validated['start_date'], $validated['end_date'], $dateBlocker->package_id, $dateBlocker->id)) {
            $overlappingBlocker = DateBlocker::where('package_id', $dateBlocker->package_id)
                ->where('id', '!=', $dateBlocker->id)
                ->where('start_date', '<=', $validated['end_date'])
                ->where('end_date', '>=', $validated['start_date'])
                ->first();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'This date range overlaps with an existing date blocker for this package in the date range of ' . 
                        Carbon::parse($overlappingBlocker->start_date)->format('d-m-Y') . ' to ' . 
                        Carbon::parse($overlappingBlocker->end_date)->format('d-m-Y')
                ], 422);
            }

            return back()->withErrors([
                'date_range' => 'This date range overlaps with an existing date blocker for this package in the date range of ' . 
                    Carbon::parse($overlappingBlocker->start_date)->format('d-m-Y') . ' to ' . 
                    Carbon::parse($overlappingBlocker->end_date)->format('d-m-Y')
            ]);
        }

        try {
            $dateBlocker->update($validated);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Date blocker updated successfully',
                    'dateBlocker' => $dateBlocker
                ]);
            }

            return back()->with('success', 'Date blocker updated successfully');
        } catch (\Exception $e) {
            Log::error('Date blocker update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Error updating date blocker: ' . $e->getMessage()], 500);
            }

            return back()->withErrors(['error' => 'Error updating date blocker: ' . $e->getMessage()]);
        }
    }

    public function destroy(DateBlocker $dateBlocker)
    {
        try {
            $dateBlocker->delete();
            
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Date blocker deleted successfully']);
            }

            return back()->with('success', 'Date blocker deleted successfully');
        } catch (\Exception $e) {
            Log::error('Date blocker deletion error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'date_blocker_id' => $dateBlocker->id
            ]);

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Error deleting date blocker: ' . $e->getMessage()], 500);
            }

            return back()->withErrors(['error' => 'Error deleting date blocker: ' . $e->getMessage()]);
        }
    }
} 