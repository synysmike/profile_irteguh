@extends('admin.layout')

@section('title', 'Edit Tim GitHub - Admin')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.github-teams.index') }}" class="text-purple-600 hover:text-purple-800 mb-4 inline-block">← Kembali</a>
    <h2 class="text-3xl font-bold text-gray-800 mb-2">Edit Tim GitHub</h2>
    <p class="text-gray-600">{{ $team->name }}</p>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 p-6 max-w-2xl">
    <form action="{{ route('admin.github-teams.update', $team) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.github-teams._form')
        <div class="flex gap-4">
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">Perbarui</button>
            <a href="{{ route('admin.github-teams.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
