<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalaryComponentController extends Controller
{
    public function index()
    {
        return Inertia::render('Payroll/SalaryComponents/Index', [
            'components' => SalaryComponent::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:allowance,deduction',
            'amount_type' => 'required|in:fixed,percent',
            'amount' => 'required|numeric|min:0',
        ]);

        SalaryComponent::create($validated);

        return redirect()->back()->with('success', 'Component created successfully.');
    }

    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:allowance,deduction',
            'amount_type' => 'required|in:fixed,percent',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $salaryComponent->update($validated);

        return redirect()->back()->with('success', 'Component updated successfully.');
    }

    public function destroy(SalaryComponent $salaryComponent)
    {
        $salaryComponent->delete();
        return redirect()->back()->with('success', 'Component deleted successfully.');
    }
}
