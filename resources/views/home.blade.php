<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Histaryo - Home</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4eafa;
            color: #1a1a1a;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px 60px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #6e4b3a;
        }

        nav a {
            text-decoration: none;
            color: #a8744f;
            margin-left: 25px;
            font-weight: 500;
        }

        .hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 60px;
            flex-wrap: wrap;
        }

        .hero-text {
            max-width: 550px;
        }

        .tag {
            background-color: #6e4b3a;
            color: white;
            padding: 6px 16px;
            border-radius: 15px;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .hero-text h1 {
            font-size: 44px;
            margin-bottom: 20px;
        }

        .hero-text p {
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            margin-bottom: 30px;
        }

        .btn {
            background-color: #6e4b3a;
            color: white;
            padding: 10px 24px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background-color: #a8744f;
        }

        .hero-image {
            width: 420px;
            margin-top: 20px;
        }

        .image-stack {
            position: relative;
            width: 100%;
            height: auto;
        }

        .bg-frame {
            position: absolute;
            top: 15px;
            left: 15px;
            width: 100%;
            border-radius: 12px;
            z-index: 1;
        }

        .main-image {
            position: relative;
            width: 100%;
            border-radius: 12px;
            z-index: 2;
            transform: rotate(-2deg);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 960px) {
            .hero {
                flex-direction: column;
                text-align: center;
            }

            .hero-image {
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Histaryo</div>
    <nav>
        <a href="{{ route('home') }}">Home</a>
        <a href="{{ route('about') }}">About</a>
        <a href="{{ route('login') }}">Login</a>
        <a href="{{ route('register') }}">Register</a>
    </nav>
</header>

<div class="hero">
    <div class="hero-text">
        <div class="tag">DIGITAL HERITAGE EXPERIENCE</div>
        <h1>Discover Cebu Like Never Before</h1>
        <p>
            Histaryo is an augmented reality-powered platform that brings Cebu’s rich cultural heritage to life.
            Whether you're a tourist, student, or local, explore landmarks through interactive AR, historical overlays, and gamified tours — all from your mobile device.
        </p>
        <a href="{{ route('about') }}" class="btn">Learn More</a>
    </div>

    <div class="hero-image">
        <div class="image-stack">
            <img src="{{ asset('images/color.jpg') }}" class="bg-frame" alt="Color Frame">
            <img src="{{ asset('images/magellancross.jpg') }}" class="main-image" alt="Cebu Landmark">
        </div>
    </div>
</div>

</body>
</html>
