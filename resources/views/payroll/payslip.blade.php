<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $item->employee->first_name }} {{ $item->employee->last_name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #1C0D82; padding-bottom: 20px; margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: bold; color: #1C0D82; margin-bottom: 5px; }
        .payslip-title { font-size: 18px; color: #666; text-transform: uppercase; letter-spacing: 2px; }
        
        .info-grid { width: 100%; margin-bottom: 30px; }
        .info-grid td { vertical-align: top; width: 50%; }
        .label { font-size: 10px; color: #999; text-transform: uppercase; font-weight: bold; margin-bottom: 2px; }
        .value { font-size: 14px; font-weight: bold; }

        .calc-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .calc-table th { background: #f8fafc; text-align: left; padding: 10px; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        .calc-table td { padding: 10px; border-bottom: 1px dashed #e2e8f0; font-size: 13px; }
        .calc-table .amount { text-align: right; font-family: monospace; font-weight: bold; }
        
        .net-box { background: #1C0D82; color: white; padding: 20px; border-radius: 10px; text-align: right; }
        .net-label { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; }
        .net-amount { font-size: 28px; font-weight: bold; }
        
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #999; }
        
        .grid-2 { width: 100%; }
        .grid-2 td { width: 50%; vertical-align: top; padding: 0 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">GORITMI HRMS</div>
        <div class="payslip-title">Salary Certificate / Payslip</div>
        <div style="font-size: 12px; color: #999; margin-top: 5px;">Period: {{ $run->month }}/{{ $run->year }}</div>
    </div>

    <table class="info-grid">
        <tr>
            <td>
                <div class="label">Employee Name</div>
                <div class="value">{{ $item->employee->first_name }} {{ $item->employee->last_name }}</div>
                <div class="label" style="margin-top: 10px;">Employee ID</div>
                <div class="value">{{ $item->employee->employee_id }}</div>
            </td>
            <td style="text-align: right;">
                <div class="label">Department</div>
                <div class="value">{{ $item->employee->department->name ?? 'N/A' }}</div>
                <div class="label" style="margin-top: 10px;">Designation</div>
                <div class="value">{{ $item->employee->designation->name ?? 'N/A' }}</div>
            </td>
        </tr>
    </table>

    <table class="grid-2">
        <tr>
            <td style="padding-left: 0;">
                <table class="calc-table">
                    <thead>
                        <tr>
                            <th>Earnings</th>
                            <th class="amount">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Basic Salary</td>
                            <td class="amount">${{ number_format($item->base_salary, 2) }}</td>
                        </tr>
                        @foreach($item->calculation_snapshot['allowances'] as $allow)
                        <tr>
                            <td>{{ $allow['name'] }}</td>
                            <td class="amount" style="color: #10b981;">+${{ number_format($allow['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
            <td style="padding-right: 0;">
                <table class="calc-table">
                    <thead>
                        <tr>
                            <th>Deductions</th>
                            <th class="amount">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item->calculation_snapshot['deductions'] as $dd)
                        <tr>
                            <td>{{ $dd['name'] }}</td>
                            <td class="amount" style="color: #ef4444;">-${{ number_format($dd['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                        @foreach($item->calculation_snapshot['loans'] as $loan)
                        <tr>
                            <td>Loan Installment</td>
                            <td class="amount" style="color: #ef4444;">-${{ number_format($loan['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                        @if($item->attendance_deduction > 0)
                        <tr>
                            <td>Attendance Deduction</td>
                            <td class="amount" style="color: #ef4444;">-${{ number_format($item->attendance_deduction, 2) }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <div class="net-box">
        <div class="net-label">Net Payable Amount</div>
        <div class="net-amount">${{ number_format($item->net_salary, 2) }}</div>
    </div>

    @if($item->employee->salaryProfile)
    <div style="margin-top: 30px; font-size: 11px; color: #666; border-top: 1px solid #eee; padding-top: 15px;">
        <span style="font-weight: bold; text-transform: uppercase; font-size: 9px; color: #999; display: block; margin-bottom: 5px;">Payment Details</span>
        <strong>Method:</strong> {{ strtoupper($item->employee->salaryProfile->payment_method) }} &nbsp;&nbsp;
        @if($item->employee->salaryProfile->payment_method === 'bank')
            <strong>Bank:</strong> {{ $item->employee->salaryProfile->bank_name }} &nbsp;&nbsp;
            <strong>A/C:</strong> {{ $item->employee->salaryProfile->account_number }}
        @endif
    </div>
    @endif

    <div class="footer">
        This is a computer generated document and does not require a physical signature.<br>
        Generated on {{ date('Y-m-d H:i:s') }}
    </div>
</body>
</html>
