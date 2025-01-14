<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
</head>
<body>
    <h1>Your Verification Code</h1>
    <p>Hello {{ $user->name }},</p>
    <p>Your verification code is: <strong>{{ $user->verification_code }}</strong></p>
    <p>Please enter this code on your verification page to complete registration.</p>
</body>
</html>
