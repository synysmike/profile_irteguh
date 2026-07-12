@extends('admin.layout')

@section('title', 'Visitor Counter - Admin')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Visitor Counter</h2>
        <p class="text-gray-600">Monitor kunjungan: IP, kota, provinsi, negara, perangkat, dan halaman</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
    <form method="GET" action="{{ route('admin.visitors.index') }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rentang Waktu</label>
            <select name="range" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="7" {{ (string) $dateRange === '7' ? 'selected' : '' }}>7 Hari Terakhir</option>
                <option value="30" {{ (string) $dateRange === '30' ? 'selected' : '' }}>30 Hari Terakhir</option>
                <option value="90" {{ (string) $dateRange === '90' ? 'selected' : '' }}>90 Hari Terakhir</option>
                <option value="365" {{ (string) $dateRange === '365' ? 'selected' : '' }}>1 Tahun Terakhir</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $startDate?->format('Y-m-d') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
            <input type="date" name="end_date" value="{{ $endDate?->format('Y-m-d') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>
        <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">Filter</button>
        <a href="{{ route('admin.visitors.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">Reset</a>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <p class="text-sm text-gray-600 mb-1">Total Kunjungan</p>
        <p class="text-3xl font-bold text-gray-800">{{ number_format($totalVisits) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <p class="text-sm text-gray-600 mb-1">Pengunjung Unik</p>
        <p class="text-3xl font-bold text-purple-700">{{ number_format($uniqueVisitors) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <p class="text-sm text-gray-600 mb-1">Hari Ini</p>
        <p class="text-3xl font-bold text-blue-700">{{ number_format($todayVisits) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <p class="text-sm text-gray-600 mb-1">Unik Hari Ini</p>
        <p class="text-3xl font-bold text-green-700">{{ number_format($todayUnique) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Kunjungan Harian</h3>
        <canvas id="visitsChart" height="150"></canvas>
        @if($visitsByDate->isEmpty())
        <p class="text-sm text-gray-500 text-center py-8">Belum ada data kunjungan pada rentang ini.</p>
        @endif
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Perangkat</h3>
        <div class="space-y-4">
            @forelse($deviceStats as $device)
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm text-gray-700 capitalize">{{ $device->device_type }}</span>
                    <span class="text-sm font-semibold text-gray-800">{{ number_format($device->visits) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full" style="width: {{ ($device->visits / max($totalVisits, 1)) * 100 }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500">Belum ada data perangkat.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Kunjungan Terbaru</h3>
        <p class="text-sm text-gray-500 mt-1">IP, kota, provinsi, dan negara per kunjungan</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kota</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provinsi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Negara</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perangkat</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Browser</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Halaman</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($recentVisits as $visit)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-900">{{ $visit->ip_address }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $visit->city ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $visit->province ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $visit->country ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 capitalize">{{ $visit->device_type ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $visit->browser ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">
                        <a href="{{ $visit->page_url }}" target="_blank" class="text-purple-600 hover:text-purple-800 truncate block max-w-[220px]">
                            {{ Str::limit($visit->page_url, 40) }}
                        </a>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $visit->visited_at?->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">Belum ada kunjungan. Buka halaman publik untuk mulai merekam.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Negara</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Negara</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unik</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($visitsByCountry as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row->country }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($row->unique_visitors) }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($row->total_visits) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500 text-sm">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Provinsi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provinsi</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unik</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($visitsByProvince as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-gray-900">{{ $row->province }}</div>
                            <div class="text-xs text-gray-500">{{ $row->country }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($row->unique_visitors) }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($row->total_visits) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500 text-sm">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Kota</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kota</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unik</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($visitsByCity as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-gray-900">{{ $row->city }}</div>
                            <div class="text-xs text-gray-500">{{ collect([$row->province, $row->country])->filter()->implode(', ') }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($row->unique_visitors) }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($row->total_visits) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500 text-sm">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Halaman Paling Banyak Dikunjungi</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Kunjungan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($mostVisitedPages as $page)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900 break-all">{{ $page->page_url }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($page->visits) }}</td>
                </tr>
                @empty
                <tr><td colspan="2" class="px-6 py-6 text-center text-gray-500">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Browser</h3>
        <div class="space-y-3">
            @forelse($browserStats as $browser)
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm text-gray-700">{{ $browser->browser }}</span>
                    <span class="text-sm font-semibold text-gray-800">{{ number_format($browser->visits) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($browser->visits / max($totalVisits, 1)) * 100 }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500">Belum ada data.</p>
            @endforelse
        </div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Platform</h3>
        <div class="space-y-3">
            @forelse($platformStats as $platform)
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm text-gray-700">{{ $platform->platform }}</span>
                    <span class="text-sm font-semibold text-gray-800">{{ number_format($platform->visits) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($platform->visits / max($totalVisits, 1)) * 100 }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500">Belum ada data.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const visitsData = @json($visitsByDate);
    const ctx = document.getElementById('visitsChart');
    if (!ctx || !visitsData.length) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: visitsData.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            }),
            datasets: [
                {
                    label: 'Total Kunjungan',
                    data: visitsData.map(item => item.total_visits),
                    backgroundColor: 'rgba(147, 51, 234, 0.55)',
                    borderColor: 'rgb(147, 51, 234)',
                    borderWidth: 1
                },
                {
                    label: 'Pengunjung Unik',
                    data: visitsData.map(item => item.unique_visitors),
                    backgroundColor: 'rgba(37, 99, 235, 0.4)',
                    borderColor: 'rgb(37, 99, 235)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
});
</script>
@endpush
