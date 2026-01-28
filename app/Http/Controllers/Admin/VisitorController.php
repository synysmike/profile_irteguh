<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $dateRange = $request->get('range', '30'); // days
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Set date range
        if ($startDate && $endDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
        } else {
            $endDate = Carbon::now()->endOfDay();
            $startDate = Carbon::now()->subDays($dateRange)->startOfDay();
        }

        // Get statistics
        $totalVisits = Visit::whereBetween('visited_at', [$startDate, $endDate])->count();
        $uniqueVisitors = Visit::whereBetween('visited_at', [$startDate, $endDate])
            ->distinct('ip_address')
            ->count('ip_address');

        // Get recent visits
        $recentVisits = Visit::whereBetween('visited_at', [$startDate, $endDate])
            ->orderBy('visited_at', 'desc')
            ->limit(100)
            ->get();

        // Get statistics by country
        $visitsByCountry = Visit::selectRaw('country, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('total_visits')
            ->limit(20)
            ->get();

        // Get statistics by city
        $visitsByCity = Visit::selectRaw('city, country, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('city')
            ->groupBy('city', 'country')
            ->orderByDesc('total_visits')
            ->limit(20)
            ->get();

        // Get most visited pages
        $mostVisitedPages = Visit::selectRaw('page_url, COUNT(*) as visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('page_url')
            ->groupBy('page_url')
            ->orderByDesc('visits')
            ->limit(20)
            ->get();

        // Get visits by date for chart
        $visitsByDate = Visit::selectRaw('DATE(visited_at) as date, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get device statistics
        $deviceStats = Visit::selectRaw('device_type, COUNT(*) as visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderByDesc('visits')
            ->get();

        // Get browser statistics
        $browserStats = Visit::selectRaw('browser, COUNT(*) as visits')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('visits')
            ->limit(10)
            ->get();

        // Get platform statistics
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
            'recentVisits',
            'visitsByCountry',
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
