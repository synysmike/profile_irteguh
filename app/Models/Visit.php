<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Visit extends Model
{
    protected $fillable = [
        'ip_address',
        'user_agent',
        'page_url',
        'referrer',
        'country',
        'city',
        'device_type',
        'browser',
        'platform',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    /**
     * Get unique visitors count for a date range
     */
    public static function getUniqueVisitorsCount($startDate = null, $endDate = null)
    {
        $query = static::select('ip_address')
            ->distinct();

        if ($startDate) {
            $query->where('visited_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('visited_at', '<=', $endDate);
        }

        return $query->count('ip_address');
    }

    /**
     * Get total page views for a date range
     */
    public static function getTotalPageViews($startDate = null, $endDate = null)
    {
        $query = static::query();

        if ($startDate) {
            $query->where('visited_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('visited_at', '<=', $endDate);
        }

        return $query->count();
    }

    /**
     * Get visits grouped by country
     */
    public static function getVisitsByCountry($limit = 10)
    {
        return static::selectRaw('country, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('total_visits')
            ->limit($limit)
            ->get();
    }

    /**
     * Get visits grouped by city
     */
    public static function getVisitsByCity($limit = 10)
    {
        return static::selectRaw('city, country, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
            ->whereNotNull('city')
            ->groupBy('city', 'country')
            ->orderByDesc('total_visits')
            ->limit($limit)
            ->get();
    }

    /**
     * Get most visited pages
     */
    public static function getMostVisitedPages($limit = 10)
    {
        return static::selectRaw('page_url, COUNT(*) as visits')
            ->whereNotNull('page_url')
            ->groupBy('page_url')
            ->orderByDesc('visits')
            ->limit($limit)
            ->get();
    }

    /**
     * Get visits by date (for chart)
     */
    public static function getVisitsByDate($days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return static::selectRaw('DATE(visited_at) as date, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
            ->where('visited_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
