<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Project;
use App\Models\Sale;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /invoice',
            'Disallow: /ulasan',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function index(): Response
    {
        $urls = [];

        $static = [
            ['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('about'), 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => route('services'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => route('portfolio.index'), 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => route('sales.public.index'), 'changefreq' => 'weekly', 'priority' => '0.7'],
            ['loc' => route('news.index'), 'changefreq' => 'daily', 'priority' => '0.8'],
            ['loc' => route('contact'), 'changefreq' => 'monthly', 'priority' => '0.6'],
        ];

        foreach ($static as $item) {
            $urls[] = $item + ['lastmod' => now()->toAtomString()];
        }

        Project::portfolioPublic()
            ->whereNotNull('slug')
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->each(function (Project $project) use (&$urls) {
                $urls[] = [
                    'loc' => route('portfolio.show', $project->slug),
                    'lastmod' => optional($project->updated_at)->toAtomString() ?: now()->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ];
            });

        Sale::publicCatalog()
            ->orderByDesc('updated_at')
            ->get(['id', 'updated_at'])
            ->each(function (Sale $sale) use (&$urls) {
                $urls[] = [
                    'loc' => route('sales.public.show', $sale),
                    'lastmod' => optional($sale->updated_at)->toAtomString() ?: now()->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                ];
            });

        News::published()
            ->orderByDesc('published_at')
            ->get(['slug', 'updated_at', 'published_at'])
            ->each(function (News $news) use (&$urls) {
                $urls[] = [
                    'loc' => route('news.show', $news->slug),
                    'lastmod' => optional($news->updated_at ?? $news->published_at)->toAtomString() ?: now()->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            });

        $xml = view('public.sitemap', compact('urls'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
