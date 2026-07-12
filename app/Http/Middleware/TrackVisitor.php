<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin/*') || $request->ajax() || $request->is('login') || $request->is('logout')) {
            return $next($request);
        }

        $ipAddress = $this->getIpAddress($request);
        if (empty($ipAddress)) {
            return $next($request);
        }

        $cacheKey = 'visit_' . md5($ipAddress . '|' . $request->path());
        if (Cache::has($cacheKey)) {
            return $next($request);
        }

        $userAgent = $request->userAgent();
        $parsed = $this->parseUserAgent($userAgent);
        $location = $this->getLocationFromIp($ipAddress);

        try {
            Visit::create([
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? mb_substr($userAgent, 0, 500) : null,
                'page_url' => mb_substr($request->fullUrl(), 0, 500),
                'referrer' => $request->header('referer') ? mb_substr($request->header('referer'), 0, 500) : null,
                'country' => $location['country'] ?? null,
                'province' => $location['province'] ?? null,
                'city' => $location['city'] ?? null,
                'device_type' => $parsed['device_type'],
                'browser' => $parsed['browser'],
                'platform' => $parsed['platform'],
                'visited_at' => now(),
            ]);

            Cache::put($cacheKey, true, 60);
        } catch (\Throwable $e) {
            Log::error('Failed to track visitor: ' . $e->getMessage());
        }

        return $next($request);
    }

    private function getIpAddress(Request $request): string
    {
        $forwarded = $request->header('X-Forwarded-For');
        if (!empty($forwarded)) {
            return trim(explode(',', $forwarded)[0]);
        }

        $realIp = $request->header('X-Real-IP');
        if (!empty($realIp)) {
            return trim($realIp);
        }

        return (string) $request->ip();
    }

    private function parseUserAgent(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return [
                'device_type' => 'unknown',
                'browser' => 'unknown',
                'platform' => 'unknown',
            ];
        }

        $deviceType = 'desktop';
        $browser = 'unknown';
        $platform = 'unknown';

        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $userAgent)) {
            $deviceType = 'mobile';
        }

        if (preg_match('/edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            $browser = 'Opera';
        }

        if (preg_match('/windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }

    private function getLocationFromIp(string $ipAddress): array
    {
        if ($this->isPrivateIp($ipAddress)) {
            return [
                'country' => 'Local',
                'province' => 'Local Network',
                'city' => 'Localhost',
            ];
        }

        return Cache::remember('geo_ip_' . $ipAddress, now()->addDays(7), function () use ($ipAddress) {
            try {
                $response = Http::timeout(3)
                    ->get("http://ip-api.com/json/{$ipAddress}", [
                        'fields' => 'status,country,regionName,city',
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? null) === 'success') {
                        return [
                            'country' => $data['country'] ?? null,
                            'province' => $data['regionName'] ?? null,
                            'city' => $data['city'] ?? null,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to get location from IP: ' . $e->getMessage());
            }

            return [
                'country' => null,
                'province' => null,
                'city' => null,
            ];
        });
    }

    private function isPrivateIp(string $ipAddress): bool
    {
        return !filter_var(
            $ipAddress,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
