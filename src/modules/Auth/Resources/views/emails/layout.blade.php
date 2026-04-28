<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', config('app.name'))</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 16px; color: #18181b; }
        .wrapper { width: 100%; padding: 40px 16px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .header { background-color: #18181b; padding: 32px 40px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 22px; font-weight: 600; letter-spacing: -0.3px; }
        .body { padding: 40px; }
        .body p { color: #3f3f46; font-size: 15px; line-height: 1.7; margin-bottom: 20px; }
        .body p:last-of-type { margin-bottom: 0; }
        .btn-wrapper { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background-color: #18181b; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-size: 15px; font-weight: 500; }
        .divider { border: none; border-top: 1px solid #e4e4e7; margin: 28px 0; }
        .footnote { font-size: 13px !important; color: #71717a !important; }
        .footer { background-color: #f4f4f5; padding: 24px 40px; text-align: center; }
        .footer p { color: #71717a; font-size: 13px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>{{ config('app.name') }}</h1>
            </div>
            <div class="body">
                @yield('content')
            </div>
            <div class="footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p>{{ config('app.url') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
