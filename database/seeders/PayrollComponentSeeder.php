<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalaryComponent;

class PayrollComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            // Allowances
            ['name' => 'House Rent Allowance (HRA)', 'type' => 'allowance', 'amount_type' => 'fixed', 'amount' => 5000],
            ['name' => 'Medical Allowance', 'type' => 'allowance', 'amount_type' => 'fixed', 'amount' => 200],
            ['name' => 'Conveyance Allowance', 'type' => 'allowance', 'amount_type' => 'fixed', 'amount' => 100],
            ['name' => 'Performance Bonus', 'type' => 'allowance', 'amount_type' => 'fixed', 'amount' => 0],
            
            // Deductions
            ['name' => 'Professional Tax', 'type' => 'deduction', 'amount_type' => 'fixed', 'amount' => 50],
            ['name' => 'Provident Fund (PF)', 'type' => 'deduction', 'amount_type' => 'fixed', 'amount' => 1500],
            ['name' => 'Group Insurance', 'type' => 'deduction', 'amount_type' => 'fixed', 'amount' => 25],
        ];

        foreach ($components as $component) {
            SalaryComponent::firstOrCreate(['name' => $component['name']], $component);
        }
    }
}
