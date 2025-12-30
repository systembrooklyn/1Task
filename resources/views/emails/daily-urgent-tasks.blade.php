<html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Urgent Tasks Summary</title>
</head>
<body style="background-color: #f1f5f9; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9;">
        <tbody><tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 30px; line-height: 1.6; color: #334155;">
                    <tbody><tr>
                        <td>
                            <!-- Logo: replaces Laravel logo -->
                            <div style="text-align: center; margin-bottom: 20px;">
                                <a href="https://www.1task.net" target="_blank" style="display: inline-block; text-decoration: none;">
                                    <img src="https://ik.imagekit.io/ts7pphpbz3/Subheading%20(1)%20(1).png" alt="{{ config('app.name') }}" width="120" style="max-width: 120px; height: auto; border: 0; outline: none; text-decoration: none;">
                                </a>
                            </div>

                            <h2 style="color: #1e293b; font-size: 18px; margin-top: 0; margin-bottom: 10px;">Hello {{ $userName }},</h2>

                            <div style="font-weight: 600; color: #0f172a; font-size: 16px; margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                                {{ $companyName }}
                            </div>

                            <p style="margin: 12px 0; color: #475569;">
                                You have <strong>{{ $urgentCount }}</strong> urgent task(s) where you are:
                            </p>

                            <ul style="padding-left: 20px; margin: 12px 0; color: #475569;">
                                <li style="margin: 4px 0;">Creator or Supervisor, <strong>or</strong></li>
                                <li style="margin: 4px 0;">Assigned, Consulted, or Informed via task collaboration.</li>
                            </ul>

                            <p style="margin: 12px 0; color: #475569;">Please review them as soon as possible.</p>

                            <p style="margin: 20px 0; text-align: center;">
                                <a href="https://www.1task.net/{{ $companyName }}/one-time-task?priority=urgent" style="display: inline-block; padding: 10px 20px; background-color: #A6C956; color: white !important; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px;">
                                    Go to Dashboard
                                </a>
                            </p>

                            <p style="margin: 12px 0; color: #475569;">Best Regards,<br>{{ config('app.name') }}</p>
                        </td>
                    </tr>
                </tbody></table>

                <div style="text-align: center; margin-top: 30px; font-size: 12px; color: #64748b;">
                    © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </div>
            </td>
        </tr>
    </tbody></table>

</body></html>