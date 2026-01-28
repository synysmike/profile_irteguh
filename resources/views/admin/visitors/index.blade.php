@extends('admin.layout')

@section('title', 'Statistik Pengunjung - Admin')

@section('content')
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Statistik Pengunjung</h2>
                <p class="text-gray-600">Monitor jumlah pengunjung dan asal pengunjung</p>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
            <form method="GET" action="{{ route('admin.visitors.index') }}" class="flex items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rentang Waktu</label>
                    <select name="range" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>7 Hari Terakhir</option>
                        <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>30 Hari Terakhir</option>
                        <option value="90" {{ $dateRange == '90' ? 'selected' : '' }}>90 Hari Terakhir</option>
                        <option value="365" {{ $dateRange == '365' ? 'selected' : '' }}>1 Tahun Terakhir</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
                    Filter
                </button>
                <a href="{{ route('admin.visitors.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                    Reset
                </a>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Kunjungan</p>
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($totalVisits) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Pengunjung Unik</p>
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($uniqueVisitors) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Visits by Date Chart -->
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Kunjungan Harian</h3>
                <canvas id="visitsChart" height="150"></canvas>
            </div>

            <!-- Device Statistics -->
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Perangkat</h3>
                <div class="space-y-4">
                    @foreach($deviceStats as $device)
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-700 capitalize">{{ $device->device_type }}</span>
                            <span class="text-sm font-semibold text-gray-800">{{ number_format($device->visits) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: {{ ($device->visits / max($totalVisits, 1)) * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Visits -->
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Kunjungan Terbaru</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perangkat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Browser</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Halaman</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentVisits as $visit)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                {{ $visit->ip_address }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($visit->city && $visit->country)
                                    {{ $visit->city }}, {{ $visit->country }}
                                @elseif($visit->country)
                                    {{ $visit->country }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                                {{ $visit->device_type ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $visit->browser ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <a href="{{ $visit->page_url }}" target="_blank" class="text-purple-600 hover:text-purple-800 truncate block max-w-xs">
                                    {{ Str::limit($visit->page_url, 50) }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $visit->visited_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Visits by Country -->
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Kunjungan Berdasarkan Negara</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Negara</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pengunjung Unik</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Kunjungan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($visitsByCountry as $visit)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $visit->country ?? 'Tidak Diketahui' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                {{ number_format($visit->unique_visitors) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                {{ number_format($visit->total_visits) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Visits by City -->
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Kunjungan Berdasarkan Kota</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kota</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Negara</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pengunjung Unik</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Kunjungan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($visitsByCity as $visit)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $visit->city ?? 'Tidak Diketahui' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $visit->country ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                {{ number_format($visit->unique_visitors) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                {{ number_format($visit->total_visits) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Most Visited Pages -->
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Halaman Paling Banyak Dikunjungi</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah Kunjungan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($mostVisitedPages as $page)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900 break-all">
                                {{ $page->page_url }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                {{ number_format($page->visits) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-gray-500">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Browser and Platform Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Browser Statistics -->
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Browser</h3>
                <div class="space-y-3">
                    @foreach($browserStats as $browser)
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-700">{{ $browser->browser }}</span>
                            <span class="text-sm font-semibold text-gray-800">{{ number_format($browser->visits) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($browser->visits / max($totalVisits, 1)) * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Platform Statistics -->
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Platform</h3>
                <div class="space-y-3">
                    @foreach($platformStats as $platform)
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-700">{{ $platform->platform }}</span>
                            <span class="text-sm font-semibold text-gray-800">{{ number_format($platform->visits) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($platform->visits / max($totalVisits, 1)) * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
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
    
    if (ctx && visitsData.length > 0) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: visitsData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                }),
                datasets: [{
                    label: 'Total Kunjungan',
                    data: visitsData.map(item => item.total_visits),
                    backgroundColor: 'rgba(147, 51, 234, 0.6)',
                    borderColor: 'rgb(147, 51, 234)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
