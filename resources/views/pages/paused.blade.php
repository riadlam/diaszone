<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>We’ll be back soon — DiasZone</title>
    <link rel="icon" type="image/png" href="{{ asset('storage_public/images_homepage/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Cairo, system-ui, sans-serif;
            background: linear-gradient(160deg, #faf5ff 0%, #ffffff 45%, #f3e8ff 100%);
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            max-width: 520px;
            width: 100%;
            background: #fff;
            border: 1px solid #e9d5ff;
            border-radius: 20px;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(88, 28, 135, 0.08);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f3e8ff;
            color: #6d28d9;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 6px 12px;
            border-radius: 999px;
            margin-bottom: 20px;
        }
        h1 {
            margin: 0 0 12px;
            font-size: 2rem;
            line-height: 1.2;
        }
        p {
            margin: 0;
            color: #4b5563;
            font-size: 1.05rem;
            line-height: 1.6;
        }
        .hint {
            margin-top: 24px;
            font-size: 0.9rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">Maintenance</div>
        <h1>We’ll be back soon</h1>
        <p>DiasZone is paused for a short maintenance window. Top-ups and checkout are temporarily unavailable.</p>
        <p class="hint">Please try again in a little while. Thank you for your patience.</p>
    </div>
</body>
</html>
