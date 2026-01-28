<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Visit;
use Illuminate\Support\Facades\Cache;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip tracking for admin routes and AJAX requests
        if ($request->is('admin/*') || $request->ajax()) {
            return $next($request);
        }

        // Get visitor IP address
        $ipAddress = $this->getIpAddress($request);
        
        // Skip if IP is localhost or empty
        if (empty($ipAddress) || $ipAddress === '127.0.0.1' || $ipAddress === '::1') {
            return $next($request);
        }

        // Use cache to prevent duplicate tracking within 1 minute
        $cacheKey = 'visit_' . $ipAddress . '_' . $request->path();
        if (Cache::has($cacheKey)) {
            return $next($request);
        }

        // Parse user agent
        $userAgent = $request->userAgent();
        $parsed = $this->parseUserAgent($userAgent);

        // Get geolocation from IP (simple method, can be enhanced with API)
        $location = $this->getLocationFromIp($ipAddress);

        // Store visit in database (use queue for better performance in production)
        try {
            Visit::create([
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'page_url' => $request->fullUrl(),
                'referrer' => $request->header('referer'),
                'country' => $location['country'] ?? null,
                'city' => $location['city'] ?? null,
                'device_type' => $parsed['device_type'],
                'browser' => $parsed['browser'],
                'platform' => $parsed['platform'],
                'visited_at' => now(),
            ]);

            // Cache for 1 minute to prevent duplicate tracking
            Cache::put($cacheKey, true, 60);
        } catch (\Exception $e) {
            // Log error but don't break the request
            \Log::error('Failed to track visitor: ' . $e->getMessage());
        }

        return $next($request);
    }

    /**
     * Get real IP address from request
     */
    private function getIpAddress(Request $request): string
    {
        // Check for IP from proxy/load balancer
        $ipAddress = $request->header('X-Forwarded-For');
        if (!empty($ipAddress)) {
            $ips = explode(',', $ipAddress);
            return trim($ips[0]);
        }

        $ipAddress = $request->header('X-Real-IP');
        if (!empty($ipAddress)) {
            return $ipAddress;
        }

        return $request->ip();
    }

    /**
     * Parse user agent to extract device, browser, and platform info
     */
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

        // Detect device type
        if (preg_match('/mobile|android|iphone|ipad|ipod|blackberry|iemobile|opera mini/i', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        // Detect browser
        if (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            $browser = 'Opera';
        }

        // Detect platform
        if (preg_match('/windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent) && !preg_match('/android/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }

    /**
     * Get location from IP address (simplified, can be enhanced with API)
     */
    private function getLocationFromIp(string $ipAddress): array
    {
        // For production, use a service like ipapi.co, ip-api.com, or MaxMind GeoIP2
        // This is a simplified version that returns empty for now
        // You can integrate with free APIs like:
        // - http://ip-api.com/json/{ip} (free tier: 45 requests/minute)
        // - https://ipapi.co/{ip}/json/ (free tier: 1000 requests/day)
        
        // Example implementation with ip-api.com (uncomment to use):
        /*
        try {
            $response = file_get_contents("http://ip-api.com/json/{$ipAddress}?fields=status,country,city");
            $data = json_decode($response, true);
            
            if ($data && $data['status'] === 'success') {
                return [
                    'country' => $data['country'] ?? null,
                    'city' => $data['city'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to get location from IP: ' . $e->getMessage());
        }
        */

        return [
            'country' => null,
            'city' => null,
        ];
    }
}
