<?php

namespace App\Http\Controllers;

use App\Models\ContractType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContractTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contractTypes = ContractType::orderBy('id', 'desc')->get();
        return Inertia::render('ContractTypes/Index', [
            'contractTypes' => $contractTypes
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:contract_types',
            'description' => 'nullable|string',
        ]);

        ContractType::create($request->all());

        return redirect()->back()->with('success', 'Contract Type created successfully.');
    }

    public function update(Request $request, ContractType $contractType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:contract_types,name,' . $contractType->id,
            'description' => 'nullable|string',
        ]);

        $contractType->update($request->all());

        return redirect()->back()->with('success', 'Contract Type updated successfully.');
    }

    public function destroy(ContractType $contractType)
    {
        $contractType->delete();
        return redirect()->back()->with('success', 'Contract Type deleted successfully.');
    }
}
