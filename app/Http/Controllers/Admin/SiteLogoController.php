<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteLogoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for editing the site logo and app name.
     */
    public function edit()
    {
        $logoPath = Setting::get('site_logo');
        $logoUrl = $logoPath ? asset('storage/' . ltrim(str_replace('public/', '', $logoPath), '/')) : null;
        $logoLandingWidth = Setting::logoLandingWidth();
        $logoLandingHeight = Setting::logoLandingHeight();
        $logoLandingLockRatio = Setting::logoLandingLockRatio();
        $appName = Setting::appName();
        return view('admin.site-logo.edit', compact('logoUrl', 'logoPath', 'logoLandingWidth', 'logoLandingHeight', 'logoLandingLockRatio', 'appName'));
    }

    /**
     * Update the site logo.
     */
    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'required|file|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'logo.mimes' => 'Logo harus berupa file gambar: jpeg, png, jpg, gif, atau webp.',
        ]);

        // Delete old logo if exists
        $oldPath = Setting::get('site_logo');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Store new logo
        $path = $request->file('logo')->store('logos', 'public');
        Setting::set('site_logo', $path);

        return redirect()->route('admin.site-logo.edit')
            ->with('success', 'Logo situs berhasil diperbarui.');
    }

    /**
     * Remove the site logo (revert to text).
     */
    public function destroy()
    {
        $oldPath = Setting::get('site_logo');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
        Setting::set('site_logo', null);

        return redirect()->route('admin.site-logo.edit')
            ->with('success', 'Logo situs telah dihapus.');
    }

    /**
     * Update application display name (shown below logo on invoices, etc.).
     */
    public function updateAppName(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:120',
        ], [
            'app_name.required' => 'Nama aplikasi wajib diisi.',
            'app_name.max' => 'Nama aplikasi maksimal 120 karakter.',
        ]);

        Setting::set('app_name', trim($validated['app_name']));

        return redirect()->route('admin.site-logo.edit')
            ->with('success', 'Nama aplikasi berhasil disimpan. Nama ini tampil di bawah logo pada invoice cetak.');
    }

    /**
     * Update landing page logo size only.
     */
    public function updateSize(Request $request)
    {
        $validated = $request->validate([
            'logo_landing_width' => 'required|integer|min:40|max:400',
            'logo_landing_height' => 'required|integer|min:20|max:120',
            'logo_landing_lock_ratio' => 'nullable|in:0,1',
        ], [
            'logo_landing_width.required' => 'Lebar wajib diisi.',
            'logo_landing_width.min' => 'Lebar minimal 40 px.',
            'logo_landing_width.max' => 'Lebar maksimal 400 px.',
            'logo_landing_height.required' => 'Tinggi wajib diisi.',
            'logo_landing_height.min' => 'Tinggi minimal 20 px.',
            'logo_landing_height.max' => 'Tinggi maksimal 120 px.',
        ]);

        $width = (int) $validated['logo_landing_width'];
        $height = (int) $validated['logo_landing_height'];
        $lockRatio = !empty($request->input('logo_landing_lock_ratio'));

        if ($lockRatio) {
            $size = max(40, min(120, $width, $height));
            $width = $size;
            $height = $size;
        }

        Setting::set('logo_landing_width', (string) $width);
        Setting::set('logo_landing_height', (string) $height);
        Setting::set('logo_landing_lock_ratio', $lockRatio ? '1' : '0');

        return redirect()->route('admin.site-logo.edit')
            ->with('success', 'Ukuran logo di landing page berhasil disimpan.');
    }
}
