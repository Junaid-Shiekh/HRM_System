<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EmployeeDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $documents = EmployeeDocument::with('employee')
            ->when($request->employee_id, function($query, $employeeId) {
                return $query->where('employee_id', $employeeId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $employees = Employee::select('id', 'first_name', 'last_name', 'employee_id')->get();

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'employees' => $employees,
            'filters' => $request->only(['employee_id'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       // Using modal in Index, so likely unused or redirect
       return redirect()->route('documents.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'document_type' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'file_path' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
            'expiry_date' => 'nullable|date',
        ]);

        $data = $request->except('file_path');

        if ($request->hasFile('file_path')) {
            $data['file_path'] = $request->file('file_path')->store('documents', 'public');
        }

        EmployeeDocument::create($data);

        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeDocument $employeeDocument)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeDocument $employeeDocument)
    {
         //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Notice: Laravel Route resource might pass param as 'document' but typed hinting
        // explicitly might be safer if we customize logic. here keeping it simple.
        $document = EmployeeDocument::findOrFail($id);
        
        $request->validate([
            'document_type' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            // File optional on update
            'file_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $data = $request->except('file_path');

        if ($request->hasFile('file_path')) {
             if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
            $data['file_path'] = $request->file('file_path')->store('documents', 'public');
        }

        $document->update($data);

        return redirect()->back()->with('success', 'Document updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $document = EmployeeDocument::findOrFail($id);
        
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();
        
        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
}
