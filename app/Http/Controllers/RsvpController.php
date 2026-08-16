<?php

namespace App\Http\Controllers;

use App\Models\InvitationContact;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RsvpController extends Controller
{
    public function show(string $token): View
    {
        $contact = InvitationContact::where('token', $token)->first();

        if (! $contact || $this->isExpired($contact)) {
            return view('rsvp.invalid');
        }

        $contact->update(['last_used_at' => now()]);

        return view('rsvp.show', [
            'invitation' => $contact->invitation,
            'contact' => $contact,
        ]);
    }

    public function submit(string $token, Request $request): \Illuminate\Http\RedirectResponse
{
    $contact = InvitationContact::where('token', $token)->first();

    if (! $contact || $this->isExpired($contact)) {
        abort(404);
    }

    $validated = $request->validate([
        'attendance_status' => ['required', 'in:attending,not_attending'],
        'vehicle_registration' => ['nullable', 'string', 'max:255'],
        'estimated_arrival' => ['nullable', 'string', 'max:255'],
        'estimated_departure' => ['nullable', 'string', 'max:255'],
        'submitted_by_name' => ['nullable', 'string', 'max:255'],
    ]);

    $contact->invitation->update([
        'attendance_status' => $validated['attendance_status'],
        'vehicle_registration' => $validated['vehicle_registration'],
        'estimated_arrival' => $validated['estimated_arrival'],
        'estimated_departure' => $validated['estimated_departure'],
        'submitted_by_name' => $validated['submitted_by_name'] ?: $contact->name,
        'submitted_by_email' => $contact->email,
        'submitted_at' => now(),
    ]);

    return redirect()
        ->route('rsvp.show', $token)
        ->with('success', 'Thank you — your response has been recorded.');
}

    private function isExpired(InvitationContact $contact): bool
    {
        $expiresAt = $contact->invitation->expires_at;

        return $expiresAt !== null && $expiresAt->isPast();
    }
}