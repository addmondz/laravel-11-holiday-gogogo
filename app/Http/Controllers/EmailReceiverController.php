<?php

namespace App\Http\Controllers;

use App\Models\EmailReceiver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class EmailReceiverController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailReceiver::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $emailReceivers = $query->paginate(10)->withQueryString();

        return Inertia::render('EmailReceivers/Index', [
            'emailReceivers' => $emailReceivers,
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create()
    {
        return Inertia::render('EmailReceivers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('email_receivers', 'email')->whereNull('deleted_at'),
            ],
        ]);

        EmailReceiver::create($validated);

        return redirect()->route('email-receivers.index')
            ->with('success', 'Email receiver created successfully.');
    }

    public function edit(EmailReceiver $emailReceiver)
    {
        return Inertia::render('EmailReceivers/Edit', [
            'emailReceiver' => $emailReceiver,
        ]);
    }

    public function update(Request $request, EmailReceiver $emailReceiver)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('email_receivers', 'email')->ignore($emailReceiver->id)->whereNull('deleted_at'),
            ],
        ]);

        $emailReceiver->update($validated);

        return redirect()->route('email-receivers.index')
            ->with('success', 'Email receiver updated successfully.');
    }

    public function destroy(EmailReceiver $emailReceiver)
    {
        $emailReceiver->delete();

        return redirect()->route('email-receivers.index')
            ->with('success', 'Email receiver deleted successfully.');
    }
}
