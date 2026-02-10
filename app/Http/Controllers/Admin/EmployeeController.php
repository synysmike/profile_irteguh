<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return redirect()->route('admin.keuangan.master.karyawan');
    }

    public function create()
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.employees.partials.form', ['employee' => null])->render()
            ]);
        }
        return view('admin.employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'basic_salary' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'npwp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['basic_salary'] = $validated['basic_salary'] ?? 0;
        Employee::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $employee = Employee::findOrFail($id);
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(string $id)
    {
        $employee = Employee::findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.employees.partials.form', compact('employee'))->render(),
                'data' => $employee
            ]);
        }

        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'basic_salary' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'npwp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['basic_salary'] = $validated['basic_salary'] ?? 0;
        $employee->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }
}
