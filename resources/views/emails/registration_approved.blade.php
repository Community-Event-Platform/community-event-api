<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Đăng ký được chấp nhận</title>
</head>
<body>
    <p>Xin chào {{ $attendeeName }},</p>

    <p>Chúc mừng! Đăng ký tham gia của bạn cho sự kiện "{{ $eventName }}" đã được chấp nhận.</p>

    @if($eventDate)
        <p>Thời gian: {{ $eventDate }}</p>
    @endif

    @if($eventLocation)
        <p>Địa điểm: {{ $eventLocation }}</p>
    @endif

    <p>Hẹn gặp bạn tại sự kiện!</p>

    <p>Trân trọng,<br/>EventHub Team</p>
</body>
</html>
