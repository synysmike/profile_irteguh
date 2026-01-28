<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Name -->
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama *</label>
        <input type="text" id="name" name="name" value="{{ old('name', isset($user) && $user ? $user->name : '') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Email -->
    <div class="md:col-span-2">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
        <input type="email" id="email" name="email" value="{{ old('email', isset($user) && $user ? $user->email : '') }}" required
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Password -->
    <div class="md:col-span-2">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Password {{ isset($user) && $user ? '(Kosongkan jika tidak ingin mengubah)' : '*' }}
        </label>
        <input type="password" id="password" name="password" {{ !isset($user) || !$user ? 'required' : '' }}
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Password Confirmation -->
    <div class="md:col-span-2">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
            Konfirmasi Password {{ isset($user) && $user ? '(Kosongkan jika tidak ingin mengubah)' : '*' }}
        </label>
        <input type="password" id="password_confirmation" name="password_confirmation" {{ !isset($user) || !$user ? 'required' : '' }}
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
    </div>

    <!-- Role -->
    <div class="md:col-span-2">
        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
        <select id="role" name="role" required
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            @php
                $currentRole = old('role', isset($user) && $user ? $user->role : 'staff');
                $canCreateSuperAdmin = auth()->user()->canCreateSuperAdmin();
            @endphp
            
            @if($canCreateSuperAdmin)
            <option value="super_admin" {{ $currentRole === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
            @endif
            <option value="admin" {{ $currentRole === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="staff" {{ $currentRole === 'staff' ? 'selected' : '' }}>Staff</option>
        </select>
        @if(!$canCreateSuperAdmin && isset($user) && $user && $user->isSuperAdmin())
        <p class="mt-1 text-sm text-gray-500">Anda tidak dapat mengubah role Super Admin.</p>
        @endif
    </div>
</div>
