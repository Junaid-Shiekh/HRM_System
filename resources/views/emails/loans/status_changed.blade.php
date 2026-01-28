<x-mail::message>
# Loan Request Status Update

Hello **{{ $loan->employee->first_name }}**,

This is to inform you about the status of your loan application.

<x-mail::panel>
## Request Summary
**Amount:** ${{ number_format($loan->amount, 2) }}
**Status:** {{ strtoupper($loan->status) }}
</x-mail::panel>

@if($loan->status === 'approved')
### ✅ Request Approved
Your loan request has been successfully approved!

**Repayment Schedule:**
* **Total Installments:** {{ $loan->installments }} Months
* **Monthly Installment:** ${{ number_format($loan->monthly_installment, 2) }}
* **Approval Date:** {{ $loan->approved_at ? \Carbon\Carbon::parse($loan->approved_at)->format('d M, Y') : now()->format('d M, Y') }}

The deductions will start from your next payroll cycle.
@elseif($loan->status === 'rejected')
### ❌ Request Rejected
Unfortunately, after reviewing your application, we are unable to approve your loan request at this time.

**Reason for Rejection:**
{{ $loan->rejection_reason }}
@endif

<x-mail::button :url="route('employee.dashboard')" color="success">
Go to Dashboard
</x-mail::button>

If you have any questions regarding this update, please contact the HR department.

Thanks,<br>
**{{ config('app.name') }} HR Team**
</x-mail::message>
