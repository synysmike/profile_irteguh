<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'ip_address',
        'user_agent',
        'page_url',
        'referrer',
        'country',
        'province',
        'city',
        'device_type',
        'browser',
        'platform',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function locationLabel(): string
    {
        $parts = array_filter([
            $this->city,
            $this->province,
            $this->country,
        ]);

        return $parts ? implode(', ', $parts) : 'Tidak diketahui';
    }

    public static function getUniqueVisitorsCount($startDate = null, $endDate = null)
    {
        $query = static::query()->select('ip_address')->distinct();

        if ($startDate) {
            $query->where('visited_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('visited_at', '<=', $endDate);
        }

        return $query->count('ip_address');
    }

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

    public static function getVisitsByCountry($limit = 10)
    {
        return static::selectRaw('country, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('total_visits')
            ->limit($limit)
            ->get();
    }

    public static function getVisitsByProvince($limit = 10)
    {
        return static::selectRaw('province, country, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
            ->whereNotNull('province')
            ->groupBy('province', 'country')
            ->orderByDesc('total_visits')
            ->limit($limit)
            ->get();
    }

    public static function getVisitsByCity($limit = 10)
    {
        return static::selectRaw('city, province, country, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
            ->whereNotNull('city')
            ->groupBy('city', 'province', 'country')
            ->orderByDesc('total_visits')
            ->limit($limit)
            ->get();
    }

    public static function getMostVisitedPages($limit = 10)
    {
        return static::selectRaw('page_url, COUNT(*) as visits')
            ->whereNotNull('page_url')
            ->groupBy('page_url')
            ->orderByDesc('visits')
            ->limit($limit)
            ->get();
    }

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
