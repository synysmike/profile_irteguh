@extends('admin.layout')

@section('title', 'Tambah Anggota Tim - Admin')

@section('content')
        <div class="mb-8">
            <a href="{{ route('admin.contributors.index') }}" class="text-purple-600 hover:text-purple-800 mb-4 inline-block">← Kembali</a>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Tambah Anggota Tim</h2>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 max-w-2xl">
            <form action="{{ route('admin.contributors.store') }}" method="POST" class="space-y-6">
                @csrf
                @include('admin.contributors.partials.form', ['contributor' => null, 'githubTeams' => $githubTeams])
                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">
                        Simpan
                    </button>
                    <a href="{{ route('admin.contributors.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
@endsection
