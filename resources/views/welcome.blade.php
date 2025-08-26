@extends('layouts.app')

@section('content')
<div class="hero">
    <h2>Welcome to My Laravel App</h2>
    <p>Beautiful brown theme with image backgrounds</p>
    <a href="{{ route('register') }}" class="btn">Get Started</a>
</div>

<div class="content">
    <h3>What we do</h3>
    <p>This app demonstrates a brown-themed Laravel UI layout with basic routing.</p>

    <img src="https://images.unsplash.com/photo-1556740738-b6a63e27c4df?auto=format&fit=crop&w=800&q=60" alt="Design" style="width:100%; max-width:600px; border-radius:10px;">
</div>
@endsection
