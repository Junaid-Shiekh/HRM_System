<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryProfile;
use App\Models\SalaryComponent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalaryProfileController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['department', 'salaryProfile', 'salaryComponents'])
            ->where('status', 'active')
            ->get();

        return Inertia::render('Payroll/SalaryProfiles/Index', [
            'employees' => $employees,
            'components' => SalaryComponent::where('is_active', true)->get()
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'base_salary' => 'required|numeric|min:0',
            'salary_type' => 'required|in:monthly,daily,hourly',
            'payment_method' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'account_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'iban' => 'nullable|string',
            'components' => 'nullable|array',
            'components.*.id' => 'exists:salary_components,id',
            'components.*.custom_amount' => 'nullable|numeric|min:0',
            'components.*.amount_type' => 'nullable|in:fixed,percent',
        ]);

        // 1. Update Profile
        SalaryProfile::updateOrCreate(
            ['employee_id' => $employee->id],
            collect($validated)->except('components')->toArray()
        );

        // 2. Sync Components
        $syncData = [];
        if (!empty($validated['components'])) {
            foreach ($validated['components'] as $comp) {
                if (isset($comp['id'])) {
                  $syncData[$comp['id']] = [
                    'custom_amount' => $comp['custom_amount'] ?? null,
                    'amount_type' => $comp['amount_type'] ?? null
                  ];
                }
            }
        }
        $employee->salaryComponents()->sync($syncData);

        return redirect()->back()->with('success', 'Salary profile updated.');
    }
}
