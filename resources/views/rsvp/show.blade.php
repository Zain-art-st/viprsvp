<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Independence Day Attendance Confirmation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow p-8 max-w-md w-full">
        <h1 class="text-lg font-semibold text-gray-500 mb-1">Independence Day Attendance Confirmation</h1>
        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ $invitation->vip_name }}</h2>

        @if (session('success'))
            <div class="bg-green-50 text-green-800 text-sm rounded p-3 mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('rsvp.submit', $contact->token) }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Will you be attending?</label>
                <div class="flex gap-3">
                    <label class="flex-1">
                        <input type="radio" name="attendance_status" value="attending" class="peer sr-only"
                            {{ old('attendance_status', $invitation->attendance_status) === 'attending' ? 'checked' : '' }}>
                        <div class="text-center py-2 rounded border border-gray-300 peer-checked:bg-green-600 peer-checked:text-white peer-checked:border-green-600 cursor-pointer">
                            Yes, attending
                        </div>
                    </label>
                    <label class="flex-1">
                        <input type="radio" name="attendance_status" value="not_attending" class="peer sr-only"
                            {{ old('attendance_status', $invitation->attendance_status) === 'not_attending' ? 'checked' : '' }}>
                        <div class="text-center py-2 rounded border border-gray-300 peer-checked:bg-red-600 peer-checked:text-white peer-checked:border-red-600 cursor-pointer">
                            No, not attending
                        </div>
                    </label>
                </div>
                @error('attendance_status')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle registration number</label>
                <input type="text" name="vehicle_registration"
                    value="{{ old('vehicle_registration', $invitation->vehicle_registration) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estimated arrival time</label>
                <input type="text" name="estimated_arrival"
                    value="{{ old('estimated_arrival', $invitation->estimated_arrival) }}"
                    placeholder="e.g. 9:00 AM"
                    class="w-full border border-gray-300 rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estimated departure time</label>
                <input type="text" name="estimated_departure"
                    value="{{ old('estimated_departure', $invitation->estimated_departure) }}"
                    placeholder="e.g. 1:00 PM"
                    class="w-full border border-gray-300 rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Your name</label>
                <input type="text" name="submitted_by_name"
                    value="{{ old('submitted_by_name', $contact->name) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Confirming as {{ $contact->email }}</p>
            </div>

            <button type="submit" class="w-full bg-gray-900 text-white rounded py-2.5 font-medium">
                Submit
            </button>
        </form>
    </div>
</body>
</html>