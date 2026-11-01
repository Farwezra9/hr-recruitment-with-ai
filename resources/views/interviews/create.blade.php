@extends('layouts.app')

@section('title', 'Jadwalkan Interview')

@section('content')
    <h1 class="text-2xl font-semibold mb-6" style="color: var(--ink);">Jadwalkan Interview: {{ $application->candidate->name }}</h1>

    <form method="POST" action="{{ route('interviews.store', $application) }}" class="bg-white p-6 rounded-lg border space-y-4 max-w-lg" style="border-color: var(--line);">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Interviewer</label>
            <select name="interviewer_id" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
                @foreach ($interviewers as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tipe</label>
                <select name="type" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
                    <option value="video">Video Call</option>
                    <option value="phone">Telepon</option>
                    <option value="onsite">Onsite</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Durasi (menit)</label>
                <input type="number" name="duration_minutes" value="60" min="15" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Tanggal & Waktu</label>
            <input type="datetime-local" name="scheduled_at" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Link / Lokasi</label>
            <input type="text" name="location_or_link" placeholder="mis. link Zoom atau alamat kantor" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
        </div>

        <button type="submit" class="px-5 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Jadwalkan</button>
    </form>
@endsection
