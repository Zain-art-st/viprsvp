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

    $customFields = \App\Models\CustomFieldDefinition::where('enabled', true)
        ->orderBy('sort_order')
        ->get();

    $existingValues = $contact->invitation->customFieldValues()
        ->pluck('value', 'field_key');

    return view('rsvp.show', [
        'invitation' => $contact->invitation,
        'contact' => $contact,
        'settings' => \App\Models\FormSettings::first(),
        'customFields' => $customFields,
        'existingValues' => $existingValues,
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
        'custom_fields' => ['nullable', 'array'],
        'custom_fields.*' => ['nullable', 'string', 'max:255'],
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

    foreach ($validated['custom_fields'] ?? [] as $fieldKey => $value) {
        \App\Models\CustomFieldValue::updateOrCreate(
            [
                'invitation_id' => $contact->invitation->id,
                'field_key' => $fieldKey,
            ],
            ['value' => $value],
        );
    }

    return redirect()
        ->route('rsvp.show', $token)
        ->with('success', \App\Models\FormSettings::first()->thank_you_message ?? 'Thank you — your response has been recorded.');
}

    private function isExpired(InvitationContact $contact): bool
    {
        $expiresAt = $contact->invitation->expires_at;

        return $expiresAt !== null && $expiresAt->isPast();
    }
}