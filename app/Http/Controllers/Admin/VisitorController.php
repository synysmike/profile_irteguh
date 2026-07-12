<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $dateRange = $request->get('range', '30');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($startDate && $endDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
        } else {
            $endDate = Carbon::now()->endOfDay();
            $startDate = Carbon::now()->subDays((int) $dateRange)->startOfDay();
        }

        $baseQuery = Visit::whereBetween('visited_at', [$startDate, $endDate]);

        $totalVisits = (clone $baseQuery)->count();
        $uniqueVisitors = (clone $baseQuery)->distinct('ip_address')->count('ip_address');
        $todayVisits = Visit::whereDate('visited_at', today())->count();
        $todayUnique = Visit::whereDate('visited_at', today())->distinct('ip_address')->count('ip_address');

        $recentVisits = (clone $baseQuery)
            ->orderByDesc('visited_at')
            ->limit(100)
            ->get();

        $visitsByCountry = Visit::selectRaw('country, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('total_visits')
            ->limit(20)
            ->get();

        $visitsByProvince = Visit::selectRaw('province, country, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('province')
            ->groupBy('province', 'country')
            ->orderByDesc('total_visits')
            ->limit(20)
            ->get();

        $visitsByCity = Visit::selectRaw('city, province, country, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('city')
            ->groupBy('city', 'province', 'country')
            ->orderByDesc('total_visits')
            ->limit(20)
            ->get();

        $mostVisitedPages = Visit::selectRaw('page_url, COUNT(*) as visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('page_url')
            ->groupBy('page_url')
            ->orderByDesc('visits')
            ->limit(20)
            ->get();

        $visitsByDate = Visit::selectRaw('DATE(visited_at) as date, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $deviceStats = Visit::selectRaw('device_type, COUNT(*) as visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderByDesc('visits')
            ->get();

        $browserStats = Visit::selectRaw('browser, COUNT(*) as visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('visits')
            ->limit(10)
            ->get();

        $platformStats = Visit::selectRaw('platform, COUNT(*) as visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('platform')
            ->groupBy('platform')
            ->orderByDesc('visits')
            ->limit(10)
            ->get();

        return view('admin.visitors.index', compact(
            'totalVisits',
            'uniqueVisitors',
            'todayVisits',
            'todayUnique',
            'recentVisits',
            'visitsByCountry',
            'visitsByProvince',
            'visitsByCity',
            'mostVisitedPages',
            'visitsByDate',
            'deviceStats',
            'browserStats',
            'platformStats',
            'startDate',
            'endDate',
            'dateRange'
        ));
    }
}
