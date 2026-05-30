<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Registration Approved</title>
</head>
<body>
    <p>Hello {{ $attendeeName }},</p>

    <p>Congratulations! Your registration for the event "{{ $eventName }}" has been approved.</p>

    @if($eventDate)
        <p>Thời gian: {{ $eventDate }}</p>
    @endif

    @if($eventLocation)
        <p>Location: {{ $eventLocation }}</p>
    @endif

    <p>We look forward to seeing you at the event!</p>

    <p>Trân trọng,<br/>EventHub Team</p>
</body>
</html>
