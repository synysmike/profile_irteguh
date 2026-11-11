<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HomeSeoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        $seo = Setting::homeSeo();

        return view('admin.home-seo.edit', compact('seo'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'seo_home_title' => 'required|string|max:70',
            'seo_home_description' => 'required|string|max:160',
            'seo_home_keywords' => 'nullable|string|max:255',
            'seo_home_robots' => ['required', Rule::in(['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'])],
            'seo_home_og_image' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_og_image' => 'nullable|boolean',
        ], [
            'seo_home_title.max' => 'Judul SEO idealnya maksimal 70 karakter.',
            'seo_home_description.max' => 'Deskripsi SEO idealnya maksimal 160 karakter.',
        ]);

        Setting::set('seo_home_title', trim($validated['seo_home_title']));
        Setting::set('seo_home_description', trim($validated['seo_home_description']));
        Setting::set('seo_home_keywords', trim((string) ($validated['seo_home_keywords'] ?? '')));
        Setting::set('seo_home_robots', $validated['seo_home_robots']);

        if ($request->boolean('remove_og_image')) {
            $this->deleteOgImage();
            Setting::set('seo_home_og_image', null);
        }

        if ($request->hasFile('seo_home_og_image')) {
            $this->deleteOgImage();
            $path = $request->file('seo_home_og_image')->store('seo', 'public');
            Setting::set('seo_home_og_image', $path);
        }

        return redirect()
            ->route('admin.home-seo.edit')
            ->with('success', 'Meta SEO beranda berhasil disimpan.');
    }

    private function deleteOgImage(): void
    {
        $old = Setting::get('seo_home_og_image');
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
    }
}
