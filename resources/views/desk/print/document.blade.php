<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Heebo, DejaVu Sans, sans-serif; color: #1a1a1a; font-size: 14px; }
        h1 { font-size: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { text-align: left; padding: 6px 0; border-bottom: 1px solid #e6e8eb; }
        .num { text-align: right; }
        .total { font-weight: 700; }
    </style>
</head>
<body>
    <h1>{{ $heading }}</h1>
    {!! $body !!}
    @if(!empty($footer))
        <p>{{ $footer }}</p>
    @endif
</body>
</html>
