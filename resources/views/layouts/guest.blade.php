<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TaskFlow') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            background: #f8f9fc;
        }

        /* Left branding panel */
        .auth-brand {
            width: 42%;
            background: linear-gradient(160deg, #1a1f36 0%, #2d3561 60%, #4c3f91 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            position: relative;
            overflow: hidden;
        }

        .auth-brand::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(91,94,244,.25) 0%, transparent 70%);
            bottom: -100px; right: -100px;
            border-radius: 50%;
        }

        .auth-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            margin-bottom: 40px;
        }

        .auth-logo-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, #5b5ef4, #8b5cf6);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }

        .auth-logo-text { font-size: 1.5rem; font-weight: 800; }

        .auth-feature {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 22px;
            color: rgba(255,255,255,.8);
        }

        .auth-feature-icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,.1);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: .9rem;
        }

        .auth-feature-title { font-size: .9rem; font-weight: 600; color: #fff; }
        .auth-feature-desc { font-size: .78rem; color: rgba(255,255,255,.5); }

        /* Right form panel */
        .auth-form-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        .auth-box {
            width: 100%;
            max-width: 420px;
        }

        .auth-box h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .auth-box .subtitle {
            color: #6b7280;
            font-size: .875rem;
            margin-bottom: 28px;
        }

        .form-label { font-weight: 500; font-size: .875rem; margin-bottom: 5px; }

        .form-control {
            padding: .65rem 1rem;
            border-radius: 9px;
            border: 1.5px solid #e5e7eb;
            font-size: .9rem;
            transition: border-color .15s;
        }

        .form-control:focus {
            border-color: #5b5ef4;
            box-shadow: 0 0 0 3px rgba(91,94,244,.12);
        }

        .btn-primary {
            background: #5b5ef4;
            border-color: #5b5ef4;
            padding: .7rem 1.5rem;
            font-weight: 600;
            border-radius: 9px;
            font-size: .9rem;
        }

        .btn-primary:hover { background: #4a4de3; border-color: #4a4de3; }

        .auth-link { color: #5b5ef4; text-decoration: none; font-weight: 500; }
        .auth-link:hover { color: #4a4de3; }

        @media (max-width: 768px) {
            .auth-brand { display: none; }
            body { background: #fff; }
        }
    </style>
</head>
<body>
    <!-- Left branding -->
    <div class="auth-brand">
        <div style="max-width:300px; width:100%;">
            <div class="auth-logo">
                <div class="auth-logo-icon"><i class="fa-solid fa-check-double text-white"></i></div>
                <div class="auth-logo-text text-white">TaskFlow</div>
            </div>
            <p class="text-white mb-4" style="opacity:.7; font-size:.9rem;">Your personal productivity system — simple, powerful, and stress-free.</p>

            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fa-solid fa-list-check"></i></div>
                <div>
                    <div class="auth-feature-title">Manage Tasks Easily</div>
                    <div class="auth-feature-desc">Create, organize and complete tasks in seconds.</div>
                </div>
            </div>
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fa-solid fa-calendar-day"></i></div>
                <div>
                    <div class="auth-feature-title">Plan Your Day</div>
                    <div class="auth-feature-desc">Hourly planner and calendar keeps you on track.</div>
                </div>
            </div>
            <div class="auth-feature">
                <div class="auth-feature-icon"><i class="fa-solid fa-chart-bar"></i></div>
                <div>
                    <div class="auth-feature-title">Track Your Progress</div>
                    <div class="auth-feature-desc">See your streaks, goals, and productivity over time.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right form -->
    <div class="auth-form-wrap">
        <div class="auth-box">
            {{ $slot }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
