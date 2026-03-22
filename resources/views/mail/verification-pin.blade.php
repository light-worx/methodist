<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your verification code</title>
  <style>
    body { font-family: sans-serif; background: #f4f4f4; margin: 0; padding: 24px; }
    .card { background: #fff; border-radius: 8px; max-width: 480px; margin: 0 auto; padding: 32px; }
    .pin { font-size: 2.5rem; font-weight: 700; letter-spacing: .3em; color: #0d6efd;
           text-align: center; margin: 24px 0; }
    .muted { color: #6c757d; font-size: .875rem; }
  </style>
</head>
<body>
  <div class="card">
    <h2 style="margin-top:0">Verify your email</h2>
    <p>Enter this code in the {{ $appName }} app to verify your email address:</p>
    <div class="pin">{{ $pin }}</div>
    <p class="muted">This code expires in 15 minutes. If you did not request this, you can safely ignore this email.</p>
  </div>
</body>
</html>