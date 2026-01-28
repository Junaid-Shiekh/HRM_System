<?php

namespace App\Mail;

use App\Models\PayrollItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public $item;

    public function __construct(PayrollItem $item)
    {
        $this->item = $item;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Salary Payslip - ' . $this->item->payrollRun->month . '/' . $this->item->payrollRun->year,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payroll.payslip',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Force eager loading for PDF consistency
        $this->item->load(['employee', 'payrollRun', 'employee.salaryProfile', 'employee.department', 'employee.designation']);

        $pdf = Pdf::loadView('payroll.payslip', [
            'run' => $this->item->payrollRun,
            'item' => $this->item
        ])->setPaper('a4', 'portrait');

        // We use the Attachment::fromData but with immediate execution to ensure it's attached correctly
        return [
            Attachment::fromData(fn () => $pdf->output(), "payslip-{$this->item->employee->employee_id}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
