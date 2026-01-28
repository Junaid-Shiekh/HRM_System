<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $headerTitle }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #1C0D82;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1C0D82;
        }
        .details-card {
            background-color: #f8f9fa;
            border-left: 4px solid #1C0D82;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 4px;
        }
        .detail-row {
            margin-bottom: 12px;
            display: flex;
        }
        .detail-label {
            font-weight: bold;
            width: 120px;
            color: #555;
            flex-shrink: 0;
        }
        .detail-value {
            color: #333;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-screening { background-color: #fef3c7; color: #92400e; }
        .status-interview { background-color: #dbeafe; color: #1e40af; }
        .status-offer { background-color: #dcfce7; color: #166534; }
        .status-hired { background-color: #dcfce7; color: #166534; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; }
        
        .message-box {
            margin-top: 20px;
            font-size: 16px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $headerTitle }}</h1>
        </div>
        
        <div class="content">
            <p class="greeting">Hello {{ $name }},</p>
            
            <div class="message-box">
                @if($status->value === 'screening')
                <p>Thank you for giving us the opportunity to review your application for the <strong>{{ $jobTitle }}</strong> position.</p>
                <p>We've received your application and our recruitment team is currently reviewing your profile and qualifications. This stage typically takes a few days as we carefully evaluate each candidate.</p>
                <p>We will be in touch with you soon regarding the next steps in our hiring process.</p>

                @elseif($status->value === 'interview')
                <p><strong>Congratulations!</strong> We were very impressed with your application and background, and we would like to invite you for an interview for the <strong>{{ $jobTitle }}</strong> position.</p>
                <p>Our recruitment team will contact you shortly via phone or email to schedule a convenient time for the interview. Please have your schedule ready!</p>
                <p>We look forward to meeting you and learning more about your skills and experience.</p>

                @elseif($status->value === 'offer')
                <p><strong>Great news!</strong> After our careful evaluation and interview process, we are thrilled to formally extend an offer to you for the <strong>{{ $jobTitle }}</strong> position at AIMANOVA!</p>
                <p>We believe your skills and experience will be a great asset to our team. Please keep an eye on your inbox, as we will be sending out the detailed offer letter and next steps very shortly.</p>
                <p>We look forward to having you join us!</p>

                @elseif($status->value === 'hired')
                <p><strong style="font-size: 20px; color: #166534;">🎉 Welcome to the Team!</strong></p>
                <p>We are absolutely delighted to confirm that you have been <strong>Hired</strong> for the <strong>{{ $jobTitle }}</strong> position at AIMANOVA!</p>
                <p>Your journey with us officially begins now. We are excited to have you on board and look forward to the great things we will achieve together.</p>
                <p>Welcome to the AIMANOVA family!</p>

                @elseif($status->value === 'rejected')
                <p>Thank you for your interest in <strong>AIMANOVA</strong> and for taking the time to apply for the <strong>{{ $jobTitle }}</strong> position.</p>
                <p>After careful consideration of your application and background, we have decided to move forward with other candidates at this time whose qualifications more closely match our current needs.</p>
                <p>We truly appreciate your interest in our company and wish you the very best in your job search and future professional endeavors.</p>
                
                @else
                <p>We wanted to update you that there has been a change in your application status for the <strong>{{ $jobTitle }}</strong> position.</p>
                <p>We are currently processing your application and will provide more details as we move forward.</p>
                @endif
            </div>

            <div class="details-card">
                <div class="detail-row">
                    <span class="detail-label">Position:</span>
                    <span class="detail-value"><strong>{{ $jobTitle }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Current Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-{{ $status->value }}">{{ $statusLabel }}</span>
                    </span>
                </div>
            </div>

            <p style="margin-top: 30px;">Best regards,<br><strong>Recruitment Team, AIMANOVA</strong></p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} AIMANOVA. All rights reserved.</p>
            <p>This is an automated message, please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
