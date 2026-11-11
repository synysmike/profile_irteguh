<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomeThemeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        $theme = Setting::homeTheme();

        return view('admin.home-theme.edit', compact('theme'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'home_kicker' => 'required|string|max:120',
            'home_tagline' => 'required|string|max:160',
            'home_description' => 'required|string|max:500',
            'home_cta_primary_label' => 'required|string|max:80',
            'home_cta_primary_url' => 'required|string|max:255',
            'home_cta_secondary_label' => 'required|string|max:80',
            'home_cta_secondary_url' => 'required|string|max:255',
            'theme_accent' => ['required', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'theme_accent_hover' => ['required', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'theme_gradient_from' => ['required', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'theme_gradient_via' => ['required', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'theme_gradient_to' => ['required', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'home_overlay_opacity' => 'required|numeric|min:0.15|max:0.75',
            'home_footer_tagline' => 'required|string|max:255',
            'home_services_heading' => 'required|string|max:120',
            'home_categories_heading' => 'required|string|max:120',
            'home_featured_heading' => 'required|string|max:120',
            'home_team_heading' => 'required|string|max:120',
            'home_show_categories' => 'nullable|boolean',
            'home_show_featured' => 'nullable|boolean',
            'home_show_services' => 'nullable|boolean',
            'home_show_team' => 'nullable|boolean',
        ], [
            'theme_accent.regex' => 'Warna aksen harus hex valid, contoh #9333ea.',
        ]);

        $stringKeys = [
            'home_kicker',
            'home_tagline',
            'home_description',
            'home_cta_primary_label',
            'home_cta_primary_url',
            'home_cta_secondary_label',
            'home_cta_secondary_url',
            'theme_accent',
            'theme_accent_hover',
            'theme_gradient_from',
            'theme_gradient_via',
            'theme_gradient_to',
            'home_footer_tagline',
            'home_services_heading',
            'home_categories_heading',
            'home_featured_heading',
            'home_team_heading',
        ];

        foreach ($stringKeys as $key) {
            $value = $validated[$key];
            if (str_starts_with($key, 'theme_')) {
                $value = Setting::normalizeHexColor($value, '#9333ea');
            }
            if (str_ends_with($key, '_url')) {
                $value = $this->normalizePublicUrl($value);
            }
            Setting::set($key, $value);
        }

        Setting::set('home_overlay_opacity', (string) round((float) $validated['home_overlay_opacity'], 2));

        foreach (['home_show_categories', 'home_show_featured', 'home_show_services', 'home_show_team'] as $toggle) {
            Setting::set($toggle, $request->boolean($toggle) ? '1' : '0');
        }

        return redirect()
            ->route('admin.home-theme.edit')
            ->with('success', 'Tema beranda berhasil disimpan.');
    }

    private function normalizePublicUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '/';
        }

        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return '/'.ltrim($url, '/');
    }
}
