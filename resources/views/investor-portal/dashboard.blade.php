<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Investor — {{ $investor->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<div class="max-w-5xl mx-auto p-4 md:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Halo, {{ $investor->name }}</h1>
            <p class="text-sm text-gray-600">Portal histori setoran modal, dividen, dan dokumen.</p>
        </div>
        <form method="POST" action="{{ route('investor.portal.logout') }}">
            @csrf
            <button class="px-3 py-2 bg-white border rounded-md text-sm">Keluar</button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg border p-4">
            <div class="text-sm text-gray-500">Total Modal Approved</div>
            <div class="text-xl font-bold">Rp {{ number_format($totalModal, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-sm text-gray-500">Dividen Diterima</div>
            <div class="text-xl font-bold text-green-700">Rp {{ number_format($dividenPaid, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-sm text-gray-500">Dividen Pending</div>
            <div class="text-xl font-bold text-amber-700">Rp {{ number_format($dividenPending, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg border overflow-hidden mb-6">
        <div class="px-4 py-3 border-b font-semibold text-gray-800">Setoran Modal</div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Invoice</th>
                        <th class="px-4 py-2 text-left">Tanggal</th>
                        <th class="px-4 py-2 text-left">Paket</th>
                        <th class="px-4 py-2 text-right">Nominal</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($injections as $row)
                    <tr>
                        <td class="px-4 py-2">{{ $row->invoice_number }}</td>
                        <td class="px-4 py-2">{{ $row->injection_date?->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ $row->benefit_name }} ({{ number_format($row->profit_share_percent, 0) }}%)</td>
                        <td class="px-4 py-2 text-right">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-2">{{ $row->statusLabel() }}</td>
                        <td class="px-4 py-2 text-right"><a class="text-purple-700" target="_blank" href="{{ $row->publicInvoiceUrl() }}">Buka</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada setoran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg border overflow-hidden mb-6">
        <div class="px-4 py-3 border-b font-semibold text-gray-800">Dividen</div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Periode</th>
                        <th class="px-4 py-2 text-right">%</th>
                        <th class="px-4 py-2 text-right">Nominal</th>
                        <th class="px-4 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($dividends as $d)
                    <tr>
                        <td class="px-4 py-2">{{ $d->period?->period_label ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($d->share_percent_snapshot, 2, ',', '.') }}%</td>
                        <td class="px-4 py-2 text-right">Rp {{ number_format($d->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-2">{{ $d->status === 'paid' ? 'Dibayar' : 'Pending' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada dividen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg border overflow-hidden">
        <div class="px-4 py-3 border-b font-semibold text-gray-800">Dokumen</div>
        <ul class="divide-y">
            @forelse($documents as $doc)
            <li class="px-4 py-3 flex items-center justify-between text-sm">
                <span>{{ $doc->title }}</span>
                <a href="{{ route('investor.portal.documents.download', $doc) }}" class="text-purple-700">Unduh</a>
            </li>
            @empty
            <li class="px-4 py-6 text-center text-gray-500 text-sm">Belum ada dokumen.</li>
            @endforelse
        </ul>
    </div>
</div>
</body>
</html>
