@extends('layouts.sidebar')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
    <h2 style="font-size: 1.75rem; font-weight: bold; margin-bottom: 1rem;">📍 All Landmarks</h2>

    @if ($landmarks->isEmpty())
        <p style="color: #6b7280;">No landmarks found.</p>
    @else
        <!-- Toggle buttons -->
        <div style="margin-bottom: 1rem; display: flex; gap: 10px;">
            <button onclick="switchView('card')" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; background: #f3f4f6; cursor: pointer;">Card View</button>
            <button onclick="switchView('list')" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; background: #f3f4f6; cursor: pointer;">List View</button>
        </div>

        <!-- Card view -->
        <div id="card-view" style="display: flex; gap: 1.5rem; overflow-x: auto; padding-bottom: 1rem;">
            @foreach ($landmarks as $landmark)
                @php
                    $data = $landmark->data();
                    $videoUrl = $data['video_url'] ?? '';
                    $embedUrl = '';

                    if (Str::contains($videoUrl, 'youtube.com/watch')) {
                        parse_str(parse_url($videoUrl, PHP_URL_QUERY), $queryParams);
                        if (isset($queryParams['v'])) {
                            $embedUrl = 'https://www.youtube.com/embed/' . $queryParams['v'];
                        }
                    } elseif (Str::contains($videoUrl, 'youtu.be')) {
                        $videoId = basename(parse_url($videoUrl, PHP_URL_PATH));
                        $embedUrl = 'https://www.youtube.com/embed/' . $videoId;
                    } else {
                        $embedUrl = $videoUrl;
                    }
                @endphp

                <div style="flex: 0 0 320px; background: white; padding: 1rem; border-radius: 10px; box-shadow: 0 6px 12px rgba(0,0,0,0.05); display: flex; flex-direction: column;">
                    <h3 style="font-size: 1.2rem; color: #1a1a1a; margin-bottom: 0.5rem;">
                        {{ $data['name'] ?? 'Unnamed Landmark' }}
                    </h3>

                    <p style="margin: 0; font-size: 0.9rem; color: #4b5563;">
                        📍 Lat: {{ $data['latitude'] ?? 'N/A' }}<br>
                        📍 Lng: {{ $data['longitude'] ?? 'N/A' }}
                    </p>

                    @if (!empty($data['description']))
                        <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #374151; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 5; -webkit-box-orient: vertical;">
                            📝 {{ $data['description'] }}
                        </p>
                    @endif

                    @if (!empty($data['image_path']))
                        <div style="margin-top: 0.75rem;">
                            <img src="{{ asset('storage/' . $data['image_path']) }}" alt="Landmark Image" style="width: 100%; border-radius: 6px;">
                        </div>
                    @endif

                    @if (!empty($embedUrl))
                        <div style="margin-top: 0.75rem;">
                            <iframe width="100%" height="180" src="{{ $embedUrl }}" frameborder="0"
                                allowfullscreen style="border-radius: 6px;"></iframe>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- List view -->
        <div id="list-view" style="display: none;">
            <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 6px 12px rgba(0,0,0,0.05);">
                <thead style="background: #f3f4f6;">
                    <tr>
                        <th style="padding: 12px; text-align: left;">Name</th>
                        <th style="padding: 12px; text-align: left;">Coordinates</th>
                        <th style="padding: 12px; text-align: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($landmarks as $index => $landmark)
                        @php $data = $landmark->data(); @endphp
                        <tr onclick="toggleRow({{ $index }})" style="cursor: pointer; border-top: 1px solid #e5e7eb;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                            <td style="padding: 12px;">{{ $data['name'] ?? 'Unnamed Landmark' }}</td>
                            <td style="padding: 12px;">📍 {{ $data['latitude'] ?? 'N/A' }}, {{ $data['longitude'] ?? 'N/A' }}</td>
                            <td style="padding: 12px; color: #2563eb;">Click to expand</td>
                        </tr>
                        <tr id="expand-{{ $index }}" style="display: none; background: #fafafa;">
                            <td colspan="3" style="padding: 15px;">
                                <strong>Description:</strong> {{ $data['description'] ?? 'No description' }} <br>
                                @if (!empty($data['image_path']))
                                    <div style="margin-top: 0.75rem;">
                                        <img src="{{ asset('storage/' . $data['image_path']) }}" alt="Landmark Image" style="width: 300px; border-radius: 6px;">
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <script>
        function switchView(view) {
            document.getElementById('card-view').style.display = (view === 'card') ? 'flex' : 'none';
            document.getElementById('list-view').style.display = (view === 'list') ? 'block' : 'none';
        }

        function toggleRow(index) {
            const row = document.getElementById('expand-' + index);
            row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
        }
    </script>
@endsection
