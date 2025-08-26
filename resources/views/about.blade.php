<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Histaryo</title>
    <style>
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

        .about-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 60px;
            flex-wrap: wrap;
        }

        .about-text {
            max-width: 550px;
        }

        .about-text h2 {
            font-size: 32px;
            margin-bottom: 20px;
        }

        .about-text p {
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            margin-bottom: 20px;
        }

        .about-image {
            width: 420px;
            margin-top: 20px;
        }

        .image-stack {
            position: relative;
            width: 100%;
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
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        @media (max-width: 960px) {
            .about-section {
                flex-direction: column;
                text-align: center;
            }

            .about-image {
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

<div class="about-section">
    <div class="about-text">
        <h2>What is Histaryo?</h2>
        <p>
            Histaryo is an AR-powered platform that transforms how locals and visitors explore Cebu’s rich cultural heritage.
            With just a mobile device, users can scan QR codes or GPS-based markers to unlock immersive AR experiences —
            including historical overlays, old photographs, trivia challenges, and gamified tours.
        </p>
        <p>
            Built to educate, entertain, and inspire, Histaryo bridges culture and technology to make history come alive like never before.
        </p>
    </div>

    <div class="about-image">
        <div class="image-stack">
            <img src="{{ asset('images/color.jpg') }}" class="bg-frame" alt="Color Frame">
            <img src="{{ asset('images/AR TOUR.jpg') }}" class="main-image" alt="Cebu Landmark">
        </div>
    </div>
</div>

</body>
</html>
