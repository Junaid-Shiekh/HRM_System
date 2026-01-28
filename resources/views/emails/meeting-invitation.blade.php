<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Invitation</title>
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
            width: 100px;
            color: #555;
            flex-shrink: 0;
        }
        .detail-value {
            color: #333;
        }
        .description {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .btn-container {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            background-color: #1C0D82;
            color: #ffffff !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #150a61;
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
            <h1>Meeting Invitation</h1>
        </div>
        
        <div class="content">
            <p class="greeting">Hello,</p>
            <p>You have been invited to a new meeting. Please confirm your availability and review the details below.</p>
            
            <div class="details-card">
                <div class="detail-row">
                    <span class="detail-label">Topic:</span>
                    <span class="detail-value"><strong>{{ $meeting->title }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($meeting->start_time)->format('l, F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Time:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($meeting->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($meeting->end_time)->format('g:i A') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Location:</span>
                    <span class="detail-value">{{ $meeting->location ?? 'Online' }}</span>
                </div>
            </div>

            @if($meeting->description)
            <div class="description">
                <strong>Description:</strong>
                <p>{{ $meeting->description }}</p>
            </div>
            @endif

            @if($meeting->meeting_link)
            <div class="detail-row">
                <span class="detail-label">Link:</span>
                <span class="detail-value"><a href="{{ $meeting->meeting_link }}" target="_blank">Join Meeting</a></span>
            </div>
            @endif
            
            <div class="btn-container">
                @if($meeting->meeting_link)
                    <a href="{{ $meeting->meeting_link }}" class="btn">Join Meeting Now</a>
                @endif
                <a href="{{ route('meetings.index') }}" class="btn" style="background-color: #6c757d; margin-left: 10px;">View Details</a>
            </div>

            <p>Best regards,<br><strong>HR Team</strong></p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} HR Management System. All rights reserved.</p>
            <p>This is an automated message, please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
