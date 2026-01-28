<x-mail::message>
# Salary Advance Request Update

Hello **{{ $advance->employee->first_name }}**,

We are writing to provide an update on your salary advance request.

<x-mail::panel>
## Request Summary
**Amount:** ${{ number_format($advance->amount, 2) }}
**Status:** {{ strtoupper($advance->status) }}
</x-mail::panel>

@if($advance->status === 'approved')
### ✅ Request Approved
Your salary advance of ${{ number_format($advance->amount, 2) }} has been approved and moved to processing.

**Deduction Details:**
* **Deduction Month:** {{ $advance->repayment_date ? \Carbon\Carbon::parse($advance->repayment_date)->format('F Y') : 'Next Payroll' }}

The full amount will be automatically deducted from your salary for the specified month.
@elseif($advance->status === 'rejected')
### ❌ Request Rejected
We regret to inform you that your salary advance request has been declined.

**Reason for Rejection:**
{{ $advance->rejection_reason }}
@endif

<x-mail::button :url="route('employee.dashboard')" color="primary">
View Dashboard
</x-mail::button>

If you have any questions, please feel free to reach out to the finance or HR team.

Best regards,<br>
**{{ config('app.name') }} Finance Team**
</x-mail::message>
