<html>
<head>
    <title>Invitation</title>
</head>
<body>
    <h1>You have been invited to join the platform!</h1>
    <p>Company: {{ $companyName }}</p>
    <p>Click the link below to complete your registration:</p>
    <a href="{{ url('/register?token=' . $invitation->token) }}">Complete Registration</a>

</body>
</html>
