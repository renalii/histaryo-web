@extends('layouts.sidebar')

@section('content')
<div style="padding:1rem;">
    <h1 style="font-size:1.8rem; font-weight:700; margin-bottom:1rem;">QR Codes Manager</h1>

    {{-- Success / Error flash --}}
    @if(session('success'))
        <div style="background:#d1fae5; color:#065f46; padding:.75rem 1rem; border-radius:6px; margin-bottom:1rem;">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:.75rem 1rem; border-radius:6px; margin-bottom:1rem;">
            {{ implode(', ', $errors->all()) }}
        </div>
    @endif

    {{-- Create new QR mapping --}}
    <div style="background:#f9fafb; padding:1rem; border-radius:8px; margin-bottom:2rem;">
        <h2 style="font-size:1.2rem; font-weight:600; margin-bottom:.75rem;">Create New QR</h2>
        <form method="POST" action="{{ route('curators.qr.store') }}">
            @csrf
            <div style="display:flex; flex-wrap:wrap; gap:1rem;">
                <div style="flex:1; min-width:200px;">
                    <label for="code" style="display:block; font-weight:500; margin-bottom:.25rem;">QR Code Text</label>
                    <input type="text" id="code" name="code" required
                        value="{{ old('code') }}"
                        style="width:100%; padding:.5rem; border:1px solid #d1d5db; border-radius:6px;">
                    <small style="color:#6b7280;">The unique text/ID embedded in the QR (e.g. L001)</small>
                </div>

                <div style="flex:1; min-width:200px;">
                    <label for="landmark_id" style="display:block; font-weight:500; margin-bottom:.25rem;">Landmark</label>
                    <select id="landmark_id" name="landmark_id" required
                        style="width:100%; padding:.5rem; border:1px solid #d1d5db; border-radius:6px;">
                        <option value="">-- Select Landmark --</option>
                        @foreach($landmarks as $lm)
                            <option value="{{ $lm['id'] }}" @selected(old('landmark_id')==$lm['id'])>
                                {{ $lm['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="flex:0 0 120px;">
                    <label for="format" style="display:block; font-weight:500; margin-bottom:.25rem;">Format</label>
                    <select id="format" name="format"
                        style="width:100%; padding:.5rem; border:1px solid #d1d5db; border-radius:6px;">
                        <option value="png" @selected(old('format')=='png')>PNG</option>
                        <option value="svg" @selected(old('format')=='svg')>SVG</option>
                    </select>
                </div>
            </div>
            <div style="margin-top:1rem;">
                <button type="submit"
                    style="background:#2563eb; color:white; padding:.5rem 1rem; border-radius:6px; font-weight:600;">
                    Create QR
                </button>
            </div>
        </form>
    </div>

    {{-- Existing QR mappings --}}
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:600px;">
            <thead>
                <tr style="background:#f3f4f6;">
                    <th style="text-align:left; padding:.75rem; font-weight:600;">Code</th>
                    <th style="text-align:left; padding:.75rem; font-weight:600;">Landmark ID</th>
                    <th style="text-align:left; padding:.75rem; font-weight:600;">Created</th>
                    <th style="padding:.75rem; font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($qrs as $qr)
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <td style="padding:.75rem; font-weight:500;">{{ e($qr['code']) }}</td>
                        <td style="padding:.75rem;">{{ e($qr['landmark_id']) }}</td>
                        <td style="padding:.75rem; color:#6b7280;">
                            @if($qr['created_at'] instanceof \Google\Cloud\Core\Timestamp)
                                {{ \Carbon\Carbon::instance($qr['created_at']->get())->diffForHumans() }}
                            @else
                                —
                            @endif
                        </td>
                        <td style="padding:.75rem; text-align:center; white-space:nowrap;">
                            <a href="{{ $qr['download_url'] }}"
                               style="color:#2563eb; margin-right:.5rem; text-decoration:underline;">Download</a>
                            <a href="{{ $qr['resolve_url'] }}"
                               target="_blank"
                               style="color:#16a34a; margin-right:.5rem; text-decoration:underline;">Open</a>
                            <form method="POST" action="{{ route('curators.qr.destroy', $qr['id']) }}"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Delete this QR mapping?')"
                                    style="color:#dc2626; background:none; border:none; padding:0; cursor:pointer; text-decoration:underline;">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding:1rem; text-align:center; color:#6b7280;">
                            No QR mappings yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
