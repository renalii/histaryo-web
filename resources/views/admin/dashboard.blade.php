@extends('layouts.sidebar')

@section('content')
@php
    use Carbon\Carbon;
    $today = Carbon::now()->format('F j, Y');
    $email = session('email');
    $name = $email ? ucfirst(explode('@', $email)[0]) : 'Admin';
@endphp

<div style="background: linear-gradient(135deg, #7e22ce, #9333ea); color: white; padding: 2rem 2.5rem; border-radius: 1rem; margin-bottom: 2.5rem;">
    <p style="margin: 0 0 0.3rem 0;">📅 {{ $today }}</p>
    <h1 style="font-size: 2rem; font-weight: 700; margin: 0;">Welcome back, {{ $name }}!</h1>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
    <div style="background: #f3e8ff; padding: 1.5rem; border-radius: 12px;">
        <h3 style="margin: 0; font-size: 1rem; color: #7e22ce;">👥 Total Users</h3>
        <p style="font-size: 1.8rem; font-weight: bold;">{{ $userCount ?? 0 }}</p>
    </div>
    <div style="background: #fef3c7; padding: 1.5rem; border-radius: 12px;">
        <h3 style="margin: 0; font-size: 1rem; color: #b45309;">🧑‍🏫 Curators</h3>
        <p style="font-size: 1.8rem; font-weight: bold;">{{ $curatorCount ?? 0 }}</p>
    </div>
    <div style="background: #d1fae5; padding: 1.5rem; border-radius: 12px;">
        <h3 style="margin: 0; font-size: 1rem; color: #047857;">🧭 Landmarks</h3>
        <p style="font-size: 1.8rem; font-weight: bold;">{{ $landmarkCount ?? 0 }}</p>
    </div>
    <div style="background: #fee2e2; padding: 1.5rem; border-radius: 12px;">
        <h3 style="margin: 0; font-size: 1rem; color: #b91c1c;">📋 Logs</h3>
        <p style="font-size: 1.8rem; font-weight: bold;">{{ $logCount ?? 0 }}</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
    
    <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <h3 style="margin-bottom: 1rem; color: #6b7280;">📈 Visits Overview</h3>
        <canvas id="visitsChart" width="400" height="300"></canvas>
    </div>

    <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <h3 style="margin-bottom: 1rem; color: #6b7280;">📊 Usage by Role</h3>
        <canvas id="roleUsageChart" width="400" height="300"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const visitsData = {
        labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        datasets: [{
            label: 'Visits',
            data: {!! json_encode($visitsByDay) !!},
            borderColor: '#7e22ce',
            backgroundColor: 'rgba(126, 34, 206, 0.1)',
            tension: 0.3,
            fill: true,
            pointRadius: 5,
            pointBackgroundColor: '#9333ea'
        }]
    };

    const usageData = {
        labels: ['Admins', 'Curators'],
        datasets: [{
            data: [{{ $adminCount ?? 0 }}, {{ $curatorCount ?? 0 }}],
            backgroundColor: ['#8b5cf6', '#f59e0b'],
            borderWidth: 1
        }]
    };

    new Chart(document.getElementById('visitsChart'), {
        type: 'line',
        data: visitsData,
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('roleUsageChart'), {
        type: 'doughnut',
        data: usageData,
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@endsection
