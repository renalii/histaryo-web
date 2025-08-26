@extends('layouts.sidebar')

@section('content')
    @php
        use Carbon\Carbon;
        $today = Carbon::now()->format('F j, Y');
        $email = session('email');
        $name = $email ? ucfirst(explode('@', $email)[0]) : 'Curator';

        // Optional: pass these from a controller; these are safe fallbacks:
        $stats = $stats ?? [
            'landmarks' => $landmarksCount ?? 0,
            'trivia' => $triviaCount ?? 0,
            'pending' => $pendingReviews ?? 0,
            'logs' => $logsCount ?? 0,
        ];

        // Optionally pass arrays for recent items
        $recentLandmarks = $recentLandmarks ?? [];
        $recentTrivia = $recentTrivia ?? [];
        $recentLogs = $recentLogs ?? [];
    @endphp

    {{-- Welcome Banner --}}
    <div style="
        background: linear-gradient(135deg, #7e22ce, #a855f7);
        color: #fff;
        padding: 2rem 2.25rem;
        border-radius: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: 0 12px 24px rgba(126, 34, 206, 0.15);
        display: flex; flex-direction: column; gap: 0.5rem;">
        <p style="margin: 0; font-size: 0.9rem; opacity: 0.85;">{{ $today }}</p>
        <h2 style="font-size: 2rem; font-weight: 700; margin: 0;">Welcome back, {{ $name }} 👋</h2>
        <p style="margin: 0; font-size: 1rem; opacity: 0.95;">
            You can manage <strong>landmarks</strong> and <strong>trivia</strong> from your dashboard.
        </p>
    </div>

    {{-- Top Stats --}}
    <div class="grid" style="display:grid; grid-template-columns: repeat(12, minmax(0,1fr)); gap: 1rem; margin-bottom:1rem;">
        <div class="card stat" style="grid-column: span 3; background:#fff; border-radius:14px; padding:1rem; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <p style="margin:0; color:#6b7280; font-size:.9rem;">Landmarks</p>
                    <h3 style="margin:.25rem 0 0 0; font-size:1.75rem; color:#4c1d95;">{{ number_format($stats['landmarks']) }}</h3>
                </div>
                <div class="pill" style="background:#f5f3ff; color:#6d28d9; padding:.4rem .6rem; border-radius:999px; font-size:.8rem;">All-time</div>
            </div>
        </div>

        <div class="card stat" style="grid-column: span 3; background:#fff; border-radius:14px; padding:1rem; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <p style="margin:0; color:#6b7280; font-size:.9rem;">Trivia</p>
                    <h3 style="margin:.25rem 0 0 0; font-size:1.75rem; color:#4c1d95;">{{ number_format($stats['trivia']) }}</h3>
                </div>
                <div class="pill" style="background:#fdf2f8; color:#be185d; padding:.4rem .6rem; border-radius:999px; font-size:.8rem;">Published</div>
            </div>
        </div>

        <div class="card stat" style="grid-column: span 3; background:#fff; border-radius:14px; padding:1rem; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <p style="margin:0; color:#6b7280; font-size:.9rem;">Pending Reviews</p>
                    <h3 style="margin:.25rem 0 0 0; font-size:1.75rem; color:#4c1d95;">{{ number_format($stats['pending']) }}</h3>
                </div>
                <div class="pill" style="background:#fff7ed; color:#c2410c; padding:.4rem .6rem; border-radius:999px; font-size:.8rem;">Needs action</div>
            </div>
        </div>

        <div class="card stat" style="grid-column: span 3; background:#fff; border-radius:14px; padding:1rem; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <p style="margin:0; color:#6b7280; font-size:.9rem;">Activity Logs</p>
                    <h3 style="margin:.25rem 0 0 0; font-size:1.75rem; color:#4c1d95;">{{ number_format($stats['logs']) }}</h3>
                </div>
                <div class="pill" style="background:#ecfeff; color:#0e7490; padding:.4rem .6rem; border-radius:999px; font-size:.8rem;">Last 30d</div>
            </div>
        </div>
    </div>

    {{-- Actions + Charts --}}
    <div class="grid" style="display:grid; grid-template-columns: repeat(12, minmax(0,1fr)); gap: 1rem; margin-bottom:1rem;">
        {{-- Quick Actions --}}
        <div class="card" style="grid-column: span 4; background:#fff; border-radius:14px; padding:1rem; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.75rem;">
                <h4 style="margin:0; color:#111827;">Quick Actions</h4>
            </div>
            <div style="display:flex; flex-direction:column; gap:.5rem;">
                <a href="{{ route('landmarks.create') }}" style="text-decoration:none; background:#8b5cf6; color:#fff; padding:.75rem 1rem; border-radius:10px; font-weight:600; text-align:center;">+ Add Landmark</a>
                <a href="{{ route('landmarks.index') }}" style="text-decoration:none; background:#f3f4f6; color:#111827; padding:.75rem 1rem; border-radius:10px; font-weight:600; text-align:center;">Manage Landmarks</a>
                @if (Route::has('trivia.index'))
                    <a href="{{ route('trivia.index') }}" style="text-decoration:none; background:#f3f4f6; color:#111827; padding:.75rem 1rem; border-radius:10px; font-weight:600; text-align:center;">Manage Trivia</a>
                @endif
                <a href="{{ route('curators.map') }}" style="text-decoration:none; background:#eef2ff; color:#3730a3; padding:.75rem 1rem; border-radius:10px; font-weight:600; text-align:center;">🗺️ View Map</a>
            </div>
        </div>

        {{-- Line Chart: Content added over time --}}
            <div class="card" style="grid-column: span 8; background:#fff; border-radius:14px; padding:1rem; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.75rem;">
                    <h4 style="margin:0; color:#111827;">Last 8 Weeks — Items Added</h4>
                    <div style="font-size:.85rem; color:#6b7280;">Landmarks & Trivia</div>
                </div>
                <canvas id="lineChart" height="110"></canvas>
            </div>

    </div>

    {{-- Two Columns: Recent + Doughnut --}}
    <div class="grid" style="display:grid; grid-template-columns: repeat(12, minmax(0,1fr)); gap: 1rem; margin-bottom:1rem;">
        {{-- Recent Landmarks --}}
        <div class="card" style="grid-column: span 7; background:#fff; border-radius:14px; padding:1rem; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.75rem;">
                <h4 style="margin:0; color:#111827;">Recent Landmarks</h4>
                <a href="{{ route('landmarks.index') }}" style="font-size:.9rem; color:#7c3aed; text-decoration:none;">View all →</a>
            </div>

            @if (empty($recentLandmarks))
                <p style="color:#6b7280; margin:.5rem 0;">No recent landmarks.</p>
            @else
                <div style="overflow:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="text-align:left; color:#6b7280; font-size:.85rem;">
                                <th style="padding:.5rem .25rem;">Name</th>
                                <th style="padding:.5rem .25rem;">Created</th>
                                <th style="padding:.5rem .25rem;">Location</th>
                                <th style="padding:.5rem .25rem; text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentLandmarks as $l)
                                <tr style="border-top:1px solid #f3f4f6;">
                                    <td style="padding:.6rem .25rem; font-weight:600; color:#111827;">{{ $l['name'] ?? 'Untitled' }}</td>
                                    <td style="padding:.6rem .25rem; color:#374151;">{{ $l['created_at'] ?? '—' }}</td>
                                    <td style="padding:.6rem .25rem; color:#374151;">{{ $l['location'] ?? (($l['latitude'] ?? '').', '.($l['longitude'] ?? '')) }}</td>
                                    <td style="padding:.6rem .25rem; text-align:right;">
                                        @if (!empty($l['id']))
                                            <a href="{{ route('landmarks.show', $l['id']) }}" style="text-decoration:none; color:#2563eb; margin-right:.5rem;">View</a>
                                            <a href="{{ route('landmarks.edit', $l['id']) }}" style="text-decoration:none; color:#92400e;">Edit</a>
                                        @else
                                            <span style="color:#9ca3af;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Doughnut Chart: Composition --}}
        <div class="card" style="grid-column: span 5; background:#fff; border-radius:14px; padding:1rem; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.75rem;">
                <h4 style="margin:0; color:#111827;">Content Mix</h4>
                <div style="font-size:.85rem; color:#6b7280;">Share by type</div>
            </div>
            <canvas id="donutChart" height="180"></canvas>
            <div style="display:flex; justify-content:center; gap:1rem; margin-top:.5rem; color:#374151; font-size:.9rem;">
                <span>🗺️ Landmarks</span>
                <span>🧠 Trivia</span>
            </div>
        </div>
    </div>

    {{-- Activity Feed & Tips --}}
    <div class="grid" style="display:grid; grid-template-columns: repeat(12, minmax(0,1fr)); gap: 1rem; margin-bottom:2rem;">
        {{-- Activity Feed --}}
        <div class="card" style="grid-column: span 7; background:#fff; border-radius:14px; padding:1rem; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.75rem;">
                <h4 style="margin:0; color:#111827;">Recent Activity</h4>
                <div style="font-size:.85rem; color:#6b7280;">Last 24 hours</div>
            </div>

            @if (empty($recentLogs))
                <p style="color:#6b7280; margin:.5rem 0;">No recent activity.</p>
            @else
                <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:.75rem;">
                    @foreach ($recentLogs as $log)
                        <li style="display:flex; gap:.75rem; align-items:flex-start;">
                            <div style="width:10px; height:10px; border-radius:999px; background:#7c3aed; margin-top:.4rem;"></div>
                            <div>
                                <div style="font-weight:600; color:#111827;">{{ $log['action'] ?? 'Action' }}</div>
                                <div style="color:#6b7280; font-size:.9rem;">{{ $log['email'] ?? 'user' }} • {{ $log['timestamp'] ?? '' }}</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Tips / Shortcuts --}}
        <div class="card" style="grid-column: span 5; background:#fff; border-radius:14px; padding:1rem; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.75rem;">
                <h4 style="margin:0; color:#111827;">Tips & Shortcuts</h4>
            </div>
            <div style="display:flex; flex-direction:column; gap:.6rem; color:#374151;">
                <div style="background:#f8fafc; border:1px dashed #e5e7eb; padding:.75rem; border-radius:10px;">
                    Use the <strong>Map</strong> view to verify coordinates visually before publishing.
                </div>
                <div style="background:#f8fafc; border:1px dashed #e5e7eb; padding:.75rem; border-radius:10px;">
                    Add a short <strong>video URL</strong> to boost engagement on landmark pages.
                </div>
                <div style="background:#f8fafc; border:1px dashed #e5e7eb; padding:.75rem; border-radius:10px;">
                    Keep descriptions concise. Aim for <strong>80–120 words</strong>.
                </div>
            </div>
        </div>
    </div>

    {{-- Page Styles --}}
    <style>
        @media (max-width: 1024px) {
            .grid > .card { grid-column: span 12 !important; }
            .grid > .stat { grid-column: span 6 !important; }
        }
        @media (max-width: 640px) {
            .grid > .stat { grid-column: span 12 !important; }
        }
        .card:hover { transform: translateY(-2px); transition: transform .15s ease, box-shadow .15s ease; box-shadow: 0 10px 24px rgba(0,0,0,0.08) !important; }
    </style>

    {{-- Charts (Chart.js) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const weeks = ['W-7','W-6','W-5','W-4','W-3','W-2','W-1','Now'];
            const sampleLandmarks = [2, 3, 1, 4, 2, 5, 3, 4];
            const sampleTrivia = [1, 2, 2, 3, 1, 2, 4, 2];

            const lineCtx = document.getElementById('lineChart').getContext('2d');
            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: weeks,
                    datasets: [
                        {
                            label: 'Landmarks',
                            data: sampleLandmarks,
                            tension: 0.35,
                            fill: false,
                            borderWidth: 2,
                        },
                        {
                            label: 'Trivia',
                            data: sampleTrivia,
                            tension: 0.35,
                            fill: false,
                            borderWidth: 2,
                        },
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false },
                    },
                    interaction: { mode: 'nearest', axis: 'x', intersect: false },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });

            const donutCtx = document.getElementById('donutChart').getContext('2d');
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Landmarks', 'Trivia'],
                    datasets: [{
                        data: [{{ (int)($stats['landmarks'] ?: 0) }}, {{ (int)($stats['trivia'] ?: 0) }}],
                        borderWidth: 0,
                    }]
                },
                options: {
                    cutout: '60%',
                    plugins: { legend: { display: false } }
                }
            });
        });
    </script>
@endsection
