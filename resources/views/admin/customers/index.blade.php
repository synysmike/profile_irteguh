@extends('admin.layout')

@section('title', 'Kelola Customer - Admin')

@section('content')
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Kelola Customer</h2>
                <p class="text-gray-600">Tambah, edit, atau hapus daftar customer</p>
            </div>
            <button onclick="openResourceModal('customerModal', 'customers', 'Customer')" class="px-6 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
                + Tambah Customer
            </button>
        </div>

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p class="text-red-600">{{ session('error') }}</p>
        </div>
        @endif

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Master Kategori Tipe Customer</h3>
            <p class="text-sm text-gray-500 mb-4">Kategori mengelompokkan tipe customer. Perilaku Individu/Perusahaan menentukan apakah field nama perusahaan ditampilkan.</p>

            <form action="{{ route('admin.customer-categories.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                @csrf
                <input type="text" name="name" placeholder="Nama kategori" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                <select name="legacy_key" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="individual">Perilaku Individu</option>
                    <option value="company">Perilaku Perusahaan</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
                    Tambah Kategori
                </button>
            </form>

            <div class="space-y-3">
                @forelse($customerCategories as $category)
                <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-center">
                    <form action="{{ route('admin.customer-categories.update', $category) }}" method="POST" class="md:col-span-5 grid grid-cols-1 md:grid-cols-5 gap-3 items-center">
                        @csrf
                        @method('PUT')
                        <input type="text" name="name" value="{{ $category->name }}" required
                               class="md:col-span-2 w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <select name="legacy_key" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="individual" {{ $category->legacy_key === 'individual' ? 'selected' : '' }}>Perilaku Individu</option>
                            <option value="company" {{ $category->legacy_key === 'company' ? 'selected' : '' }}>Perilaku Perusahaan</option>
                        </select>
                        <div class="text-sm text-gray-500">
                            {{ $category->customer_types_count }} tipe
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition text-sm">
                                Update
                            </button>
                        </div>
                    </form>
                    <form action="{{ route('admin.customer-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-50 text-red-600 rounded-md hover:bg-red-100 transition text-sm">
                            Hapus
                        </button>
                    </form>
                </div>
                @empty
                <p class="text-sm text-gray-500">Belum ada kategori tipe customer.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Master Tipe Customer</h3>

            <form action="{{ route('admin.customer-types.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                @csrf
                <input type="text" name="name" placeholder="Nama tipe customer" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                <select name="customer_category_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">— Pilih Kategori —</option>
                    @foreach($customerCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
                    Tambah Tipe
                </button>
            </form>

            <div class="space-y-3">
                @foreach($customerTypes as $type)
                <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-center">
                    <form action="{{ route('admin.customer-types.update', $type) }}" method="POST" class="md:col-span-5 grid grid-cols-1 md:grid-cols-5 gap-3 items-center">
                        @csrf
                        @method('PUT')
                        <input type="text" name="name" value="{{ $type->name }}" required
                               class="md:col-span-2 w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <select name="customer_category_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            @foreach($customerCategories as $category)
                            <option value="{{ $category->id }}" {{ (int) $type->customer_category_id === (int) $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="text-sm text-gray-500">
                            Dipakai {{ $type->customers_count }} customer
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition text-sm">
                                Update
                            </button>
                        </div>
                    </form>
                    <form action="{{ route('admin.customer-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Hapus tipe ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-50 text-red-600 rounded-md hover:bg-red-100 transition text-sm">
                            Hapus
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perusahaan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $customer)
                    <tr id="customerRow_{{ $customer->id }}">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $customer->company_name ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($customer->phone)
                            <div class="text-sm text-gray-900">{{ $customer->phone }}</div>
                            @endif
                            @if($customer->email)
                            <div class="text-sm text-gray-500">{{ $customer->email }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ ($customer->customerType?->resolveLegacyKey() ?? $customer->customer_type) === 'company' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $customer->customerType?->name ?? (($customer->customer_type === 'company') ? 'Perusahaan' : 'Individu') }}
                            </span>
                            @if($customer->customerType?->category)
                            <div class="text-xs text-gray-500 mt-1">{{ $customer->customerType->category->name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($customer->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="openResourceModal('customerModal', 'customers', 'Customer', {{ $customer->id }})" class="text-purple-600 hover:text-purple-900 mr-4">Edit</button>
                            <button onclick="deleteResource('customers', {{ $customer->id }}, 'Customer')" class="text-red-600 hover:text-red-900">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Belum ada customer. <button onclick="openResourceModal('customerModal', 'customers', 'Customer')" class="text-purple-600 hover:text-purple-800">Tambah yang pertama</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        @include('admin.components.modal', [
            'modalId' => 'customerModal',
            'title' => 'Tambah Customer'
        ])
@endsection
