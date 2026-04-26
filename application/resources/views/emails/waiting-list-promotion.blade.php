<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>You've been promoted from the waiting list!</title>
</head>
<body>
    <p>Hi {{ $user->name }},</p>

    <p>
        Great news! A spot has opened up and you have been promoted from the waiting list
        for the following workshop:
    </p>

    <p>
        <strong>{{ $workshop->title }}</strong><br>
        {{ $workshop->starts_at->format('j F Y, H:i') }} &ndash; {{ $workshop->ends_at->format('H:i') }}
    </p>

    <p>Your registration is now confirmed. We look forward to seeing you there!</p>

    <p>Best regards,<br>The Events Team</p>
</body>
</html>
