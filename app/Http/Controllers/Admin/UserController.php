<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Only super admin and admin can access
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Unauthorized access.');
        }

        $users = User::orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only super admin and admin can create users
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Unauthorized access.');
        }

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.users.partials.form', [
                    'user' => null
                ])->render()
            ]);
        }

        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only super admin and admin can create users
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Unauthorized access.');
        }

        // Check if user can create super admin
        $canCreateSuperAdmin = auth()->user()->canCreateSuperAdmin();
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['super_admin', 'admin', 'staff'])],
        ];

        // If user cannot create super admin, remove super_admin from allowed roles
        if (!$canCreateSuperAdmin && $request->role === 'super_admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk membuat Super Admin.',
                    'errors' => ['role' => ['Anda tidak memiliki izin untuk membuat Super Admin.']]
                ], 422);
            }
            return redirect()->back()
                ->withErrors(['role' => 'Anda tidak memiliki izin untuk membuat Super Admin.'])
                ->withInput();
        }

        try {
            $validated = $request->validate($rules);

            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'], // Will be hashed automatically
                'role' => $validated['role'],
                'is_admin' => in_array($validated['role'], ['super_admin', 'admin']), // For backward compatibility
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User berhasil dibuat.',
                ]);
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil dibuat.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Only super admin and admin can view users
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Unauthorized access.');
        }

        $user = User::findOrFail($id);
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Only super admin and admin can edit users
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Unauthorized access.');
        }

        $user = User::findOrFail($id);
        
        // Prevent editing own role (security)
        if ($user->id === auth()->id() && $user->role !== auth()->user()->role) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak dapat mengubah role sendiri.',
                ], 403);
            }
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat mengubah role sendiri.');
        }

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.users.partials.form', [
                    'user' => $user
                ])->render(),
                'data' => $user
            ]);
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Only super admin and admin can update users
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Unauthorized access.');
        }

        $user = User::findOrFail($id);
        $canCreateSuperAdmin = auth()->user()->canCreateSuperAdmin();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($id), 'max:255'],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'staff'])],
        ];

        // Password is optional on update
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        // Prevent user from changing their own role
        if ($user->id === auth()->id() && $request->role !== $user->role) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak dapat mengubah role sendiri.',
                    'errors' => ['role' => ['Anda tidak dapat mengubah role sendiri.']]
                ], 422);
            }
            return redirect()->back()
                ->withErrors(['role' => 'Anda tidak dapat mengubah role sendiri.'])
                ->withInput();
        }

        // Check if user can change to super admin
        if (!$canCreateSuperAdmin && $request->role === 'super_admin' && $user->role !== 'super_admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengubah role menjadi Super Admin.',
                    'errors' => ['role' => ['Anda tidak memiliki izin untuk mengubah role menjadi Super Admin.']]
                ], 422);
            }
            return redirect()->back()
                ->withErrors(['role' => 'Anda tidak memiliki izin untuk mengubah role menjadi Super Admin.'])
                ->withInput();
        }

        try {
            $validated = $request->validate($rules);

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'is_admin' => in_array($validated['role'], ['super_admin', 'admin']),
            ];

            if ($request->filled('password')) {
                $updateData['password'] = $validated['password'];
            }

            $user->update($updateData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User berhasil diperbarui.',
                ]);
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Only super admin and admin can delete users
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Unauthorized access.');
        }

        $user = User::findOrFail($id);

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak dapat menghapus akun sendiri.',
                ], 403);
            }
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        // Prevent deleting super admin if not super admin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus Super Admin.',
                ], 403);
            }
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak memiliki izin untuk menghapus Super Admin.');
        }

        $user->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
