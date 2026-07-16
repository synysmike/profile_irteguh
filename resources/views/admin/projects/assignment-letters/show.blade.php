@extends('admin.keuangan.layout')

@section('title', 'Surat Tugas ' . $letter->number)

@section('keuangan_content')
<div class="mb-6 no-print">
    <a href="{{ route('admin.projects.show', $project) }}" class="text-purple-600 hover:text-purple-800 mb-4 inline-block">← Kembali ke Detail Project</a>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Surat Tugas</h2>
            <p class="text-gray-600 mt-1">{{ $letter->number }}</p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900 text-sm font-semibold">Cetak</button>
            <a href="{{ route('admin.projects.assignment-letters.edit', [$project, $letter]) }}" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm font-semibold">Edit</a>
            <form method="POST" action="{{ route('admin.projects.assignment-letters.destroy', [$project, $letter]) }}" onsubmit="return confirm('Hapus surat tugas ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-50 text-red-700 rounded-md hover:bg-red-100 text-sm font-semibold">Hapus</button>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 no-print"><p class="text-green-700">{{ session('success') }}</p></div>
@endif

<div class="assignment-letter-sheet bg-white rounded-lg shadow border border-gray-200 p-8 max-w-3xl mx-auto print:shadow-none print:border-0 print:max-w-none">
    <div class="mb-6 pb-4 border-b border-gray-300">
        @include('components.letterhead')
    </div>

    <div class="text-center mb-8 border-b border-gray-300 pb-6">
        <h1 class="text-xl font-bold tracking-wide uppercase text-gray-900">Surat Tugas</h1>
        <p class="text-sm text-gray-600 mt-2">Nomor: <strong>{{ $letter->number }}</strong></p>
    </div>

    <div class="space-y-4 text-sm text-gray-800 leading-relaxed">
        <p>Yang bertanda tangan di bawah ini menugaskan:</p>

        <table class="w-full text-sm border border-gray-300 border-collapse mt-2">
            <thead>
                <tr class="bg-gray-50">
                    <th class="border border-gray-300 px-2 py-1.5 text-left font-semibold w-10">No</th>
                    <th class="border border-gray-300 px-2 py-1.5 text-left font-semibold">Nama</th>
                    <th class="border border-gray-300 px-2 py-1.5 text-left font-semibold">Jenis Kelamin</th>
                    <th class="border border-gray-300 px-2 py-1.5 text-left font-semibold">Nomor KTP</th>
                    <th class="border border-gray-300 px-2 py-1.5 text-left font-semibold">Nomor HP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($letter->assignees as $i => $assignee)
                <tr>
                    <td class="border border-gray-300 px-2 py-1.5 align-top">{{ $i + 1 }}</td>
                    <td class="border border-gray-300 px-2 py-1.5 align-top font-medium">{{ $assignee->name }}</td>
                    <td class="border border-gray-300 px-2 py-1.5 align-top">{{ $assignee->genderLabel() }}</td>
                    <td class="border border-gray-300 px-2 py-1.5 align-top">{{ $assignee->ktp }}</td>
                    <td class="border border-gray-300 px-2 py-1.5 align-top">{{ $assignee->phone }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p class="pt-2">Untuk melaksanakan tugas pada project berikut:</p>

        <table class="w-full text-sm">
            <tr>
                <td class="py-1.5 w-40 align-top text-gray-600">Kode Project</td>
                <td class="py-1.5 align-top w-4">:</td>
                <td class="py-1.5 align-top font-medium">{{ $project->code }}</td>
            </tr>
            <tr>
                <td class="py-1.5 align-top text-gray-600">Judul Project</td>
                <td class="py-1.5 align-top">:</td>
                <td class="py-1.5 align-top">{{ $project->title }}</td>
            </tr>
            <tr>
                <td class="py-1.5 align-top text-gray-600">Customer</td>
                <td class="py-1.5 align-top">:</td>
                <td class="py-1.5 align-top">{{ $project->customer?->name ?? '—' }}</td>
            </tr>
            @if($letter->subject)
            <tr>
                <td class="py-1.5 align-top text-gray-600">Perihal</td>
                <td class="py-1.5 align-top">:</td>
                <td class="py-1.5 align-top">{{ $letter->subject }}</td>
            </tr>
            @endif
            @if($letter->task_description)
            <tr>
                <td class="py-1.5 align-top text-gray-600">Uraian Tugas</td>
                <td class="py-1.5 align-top">:</td>
                <td class="py-1.5 align-top whitespace-pre-line">{{ $letter->task_description }}</td>
            </tr>
            @endif
            @if($letter->start_date || $letter->end_date)
            <tr>
                <td class="py-1.5 align-top text-gray-600">Periode Tugas</td>
                <td class="py-1.5 align-top">:</td>
                <td class="py-1.5 align-top">
                    {{ $letter->start_date?->format('d/m/Y') ?? '—' }}
                    s/d
                    {{ $letter->end_date?->format('d/m/Y') ?? '—' }}
                </td>
            </tr>
            @endif
        </table>

        @if($letter->notes)
        <p class="pt-2"><span class="text-gray-600">Catatan:</span> {{ $letter->notes }}</p>
        @endif

        <p class="pt-4">Demikian surat tugas ini dibuat untuk digunakan sebagaimana mestinya.</p>

        <div class="pt-10 flex justify-end">
            <div class="text-center w-56">
                <p>{{ optional($letter->letter_date)->translatedFormat('d F Y') }}</p>
                <p class="mt-1">Pemberi Tugas,</p>
                <div class="h-20"></div>
                <p class="font-semibold border-t border-gray-400 pt-2">{{ \App\Models\Setting::appName() }}</p>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, nav, aside, .keuangan-sidebar,
    .print-header-only { display: none !important; }
    .keuangan-layout { display: block !important; }
    .keuangan-main { width: 100% !important; padding: 0 !important; margin: 0 !important; }
    body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
    .container { padding-top: 0 !important; padding-bottom: 0 !important; margin-top: 0 !important; }
    .assignment-letter-sheet {
        padding: 8px 0 0 0 !important;
        margin: 0 !important;
        max-width: none !important;
        box-shadow: none !important;
        border: none !important;
    }
    .assignment-letter-sheet .letterhead-wrap,
    .assignment-letter-sheet > .mb-6:first-child {
        margin-bottom: 12px !important;
        padding-bottom: 10px !important;
    }
    @page { margin: 12mm 12mm 12mm 12mm; }
}
</style>
@endsection
