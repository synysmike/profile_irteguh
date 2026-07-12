@extends('admin.layout')

@section('title', 'Tambah Berita - Admin')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.news.index') }}" class="text-purple-600 hover:text-purple-800 text-sm">← Kembali ke Berita</a>
    <h2 class="text-3xl font-bold text-gray-800 mt-3 mb-2">Tambah Berita</h2>
    <p class="text-gray-600">Tulis konten dengan editor WYSIWYG</p>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <ul class="list-disc list-inside text-red-600 text-sm">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data" id="newsForm" class="bg-white rounded-lg shadow border border-gray-200 p-6">
    @csrf
    @include('admin.news.partials.form', ['newsItem' => null])
    <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
        <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition font-semibold">Simpan Berita</button>
        <a href="{{ route('admin.news.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition">Batal</a>
    </div>
</form>
@endsection

@include('admin.news.partials.quill-scripts')
