<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #334155; line-height: 1.6; margin: 0; padding: 0; background-color: #f8fafc; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding-bottom: 60px; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 16px; margin-top: 40px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .header { background-color: #1C0D82; padding: 40px 30px; text-align: center; color: #ffffff; }
        .logo { font-size: 28px; font-weight: 900; letter-spacing: -1px; margin: 0; }
        .logo span { opacity: 0.7; font-weight: 300; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 20px; }
        .summary-card { background-color: #f1f5f9; border-radius: 12px; padding: 25px; margin: 30px 0; border: 1px solid #e2e8f0; }
        .summary-title { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 20px; border-bottom: 1px solid #cbd5e1; padding-bottom: 10px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; }
        .summary-label { color: #64748b; font-weight: 600; }
        .summary-value { color: #1e293b; font-weight: 700; font-family: monospace; }
        .net-pay { margin-top: 20px; padding-top: 20px; border-top: 2px dashed #cbd5e1; display: flex; justify-content: space-between; align-items: center; }
        .net-label { font-size: 16px; font-weight: 800; color: #1C0D82; }
        .net-value { font-size: 24px; font-weight: 900; color: #1C0D82; }
        .attachment-note { background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; color: #1e40af; font-size: 13px; font-weight: 500; margin-top: 30px; }
        .footer { text-align: center; padding: 30px; font-size: 12px; color: #94a3b8; }
        .btn { display: inline-block; background-color: #1C0D82; color: #ffffff; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main">
            <div class="header">
                <h1 class="logo">{{ config('app.name') }} <span>HRMS</span></h1>
                <div style="margin-top: 10px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; opacity: 0.8;">
                    Monthly Salary Advice
                </div>
            </div>
            
            <div class="content">
                <div class="greeting">Hello, {{ $item->employee->first_name }}!</div>
                
                <p>We are pleased to inform you that your salary for the period <strong>{{ $item->payrollRun->month }}/{{ $item->payrollRun->year }}</strong> has been successfully processed and disbursed.</p>
                
                <div class="summary-card">
                    <div class="summary-title">Payment Summary</div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Gross Earnings</span>
                        <span class="summary-value">${{ number_format($item->base_salary + $item->total_allowances + $item->bonus, 2) }}</span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Total Deductions</span>
                        <span class="summary-value" style="color: #ef4444;">-${{ number_format($item->total_deductions + $item->loan_deduction + $item->attendance_deduction, 2) }}</span>
                    </div>
                    
                    <div class="net-pay">
                        <span class="net-label">Net Disbursement</span>
                        <span class="net-value">${{ number_format($item->net_salary, 2) }}</span>
                    </div>
                </div>

                <div style="text-align: center; margin: 40px 0;">
                    <a href="{{ route('payrolls.items.download', ['payroll' => $item->payroll_run_id, 'item' => $item->id]) }}" class="btn">
                        Download PDF Payslip
                    </a>
                </div>
                
                <div class="attachment-note">
                    <table width="100%">
                        <tr>
                            <td width="40"><span style="font-size: 24px;">📄</span></td>
                            <td>
                                <strong>Your detailed Payslip is attached!</strong><br>
                                Please find the attached PDF file (payslip-{{ $item->employee->employee_id }}.pdf) for a full breakdown of your earnings and deductions.
                            </td>
                        </tr>
                    </table>
                </div>
                
                <p style="margin-top: 40px; font-size: 14px; color: #64748b;">
                    If you have any questions regarding this payment, please contact the HR department.
                </p>
                
                <div style="margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                    <p style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b;">Best Regards,</p>
                    <p style="margin: 0; font-size: 14px; color: #64748b;">{{ config('app.name') }} HR Team</p>
                </div>
            </div>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
            This is an automated notification, please do not reply to this email.
        </div>
    </div>
</body>
</html>
