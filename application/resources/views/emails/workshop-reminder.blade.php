<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reminder: {{ $workshop->title }} is tomorrow!</title>
</head>
<body>
    <p>Hi {{ $user->name }},</p>

    <p>
        This is a friendly reminder that you are registered for a workshop
        scheduled for <strong>tomorrow</strong>:
    </p>

    <p>
        <strong>{{ $workshop->title }}</strong><br>
        {{ $workshop->starts_at->format('j F Y, H:i') }} &ndash; {{ $workshop->ends_at->format('H:i') }}
    </p>

    <p>We look forward to seeing you there!</p>

    <p>Best regards,<br>The Events Team</p>
</body>
</html>
