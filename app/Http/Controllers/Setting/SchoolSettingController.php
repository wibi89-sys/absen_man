<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Services\AuditService;
use App\Services\FaviconService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SchoolSettingController extends Controller
{
    /**
     * Show school settings form.
     */
    public function index(): View
    {
        SchoolSetting::ensureTableExists();
        $setting = SchoolSetting::current();

        return view('setting.index', compact('setting'));
    }

    /**
     * Update school settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'school_slogan' => ['nullable', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:20'],
            'nsm' => ['nullable', 'string', 'max:30'],
            'school_level' => ['required', 'string', 'max:50'],
            'principal_name' => ['required', 'string', 'max:255'],
            'principal_nip' => ['nullable', 'string', 'max:30'],
            'school_address' => ['nullable', 'string', 'max:1000'],
            'school_phone' => ['nullable', 'string', 'max:30'],
            'school_email' => ['nullable', 'email', 'max:255'],
            'school_website' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:jpeg,png,jpg,svg,webp,ico', 'max:1024'],
            'auto_convert_favicon' => ['nullable'],
            'delete_logo' => ['nullable'],
            'delete_favicon' => ['nullable'],
            'enable_demo_mode' => ['nullable'],
            'gate_welcome_text' => ['nullable', 'string', 'max:255'],
        ], [
            'school_name.required' => 'Nama madrasah/sekolah wajib diisi.',
            'school_level.required' => 'Jenjang pendidikan wajib dipilih.',
            'principal_name.required' => 'Nama kepala madrasah wajib diisi.',
            'logo.image' => 'File logo harus berupa gambar (PNG, JPG, SVG, WebP).',
            'logo.max' => 'Ukuran file logo maksimal 2MB.',
            'favicon.file' => 'File favicon harus berupa file gambar atau ikon (ICO, PNG, SVG, JPG, WebP).',
            'favicon.max' => 'Ukuran file favicon maksimal 1MB.',
        ]);

        try {
            SchoolSetting::ensureTableExists();
            $setting = SchoolSetting::current();

            // 1. Handle Explicit Custom Favicon Upload
            if ($request->hasFile('favicon')) {
                try {
                    if ($setting->favicon_path && Storage::disk('public')->exists($setting->favicon_path)) {
                        Storage::disk('public')->delete($setting->favicon_path);
                    }
                    $newFaviconPath = FaviconService::generateFavicon($request->file('favicon'));
                    if ($newFaviconPath) {
                        $validated['favicon_path'] = $newFaviconPath;
                    }
                } catch (\Throwable $favEx) {
                    Log::warning('Favicon upload failed: '.$favEx->getMessage());
                }
            }

            // 2. Handle Logo Upload (with Auto-conversion to Favicon if enabled)
            if ($request->hasFile('logo')) {
                try {
                    if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
                        Storage::disk('public')->delete($setting->logo_path);
                    }
                    $logoFile = $request->file('logo');
                    $logoPath = $logoFile->store('settings', 'public');
                    $validated['logo_path'] = $logoPath;

                    // Automatically convert uploaded logo to favicon if requested or if no manual favicon uploaded
                    if ($request->boolean('auto_convert_favicon', true) && ! $request->hasFile('favicon')) {
                        if ($setting->favicon_path && Storage::disk('public')->exists($setting->favicon_path)) {
                            Storage::disk('public')->delete($setting->favicon_path);
                        }
                        $newFaviconPath = FaviconService::generateFavicon($logoFile);
                        if ($newFaviconPath) {
                            $validated['favicon_path'] = $newFaviconPath;
                        }
                    }
                } catch (\Throwable $fileEx) {
                    Log::warning('Logo upload failed: '.$fileEx->getMessage());
                }
            }

            // 3. Handle Delete Logo Request
            if ($request->boolean('delete_logo') && $setting->logo_path) {
                try {
                    if (Storage::disk('public')->exists($setting->logo_path)) {
                        Storage::disk('public')->delete($setting->logo_path);
                    }
                } catch (\Throwable) {
                    // Ignore storage issue on delete
                }
                $validated['logo_path'] = null;
            }

            // 4. Handle Delete / Reset Favicon Request
            if ($request->boolean('delete_favicon')) {
                try {
                    if ($setting->favicon_path && Storage::disk('public')->exists($setting->favicon_path)) {
                        Storage::disk('public')->delete($setting->favicon_path);
                    }
                    FaviconService::restoreDefaultFavicon();
                } catch (\Throwable) {
                    // Ignore
                }
                $validated['favicon_path'] = null;
            }

            $validated['enable_demo_mode'] = $request->boolean('enable_demo_mode');

            unset(
                $validated['logo'],
                $validated['favicon'],
                $validated['delete_logo'],
                $validated['delete_favicon'],
                $validated['auto_convert_favicon']
            );

            $setting = SchoolSetting::updateOrCreate(['id' => 1], $validated);
            Cache::forget('school_setting');

            try {
                AuditService::log(
                    event: 'update_school_settings',
                    auditableType: SchoolSetting::class,
                    auditableId: $setting->id,
                    newValues: [
                        'school_name' => $setting->school_name,
                        'principal_name' => $setting->principal_name,
                        'enable_demo_mode' => $setting->enable_demo_mode,
                    ]
                );
            } catch (\Throwable $auditEx) {
                Log::warning('AuditService log failed: '.$auditEx->getMessage());
            }

            return redirect()->route('settings.index')->with('success', 'Profil dan identitas madrasah berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('SchoolSettingController update failed: '.$e->getMessage());

            return redirect()->route('settings.index')->withErrors(['error' => 'Gagal memperbarui pengaturan: '.$e->getMessage()]);
        }
    }

    /**
     * Convert existing school logo into a browser favicon.
     */
    public function convertLogoToFavicon(): RedirectResponse
    {
        try {
            $setting = SchoolSetting::current();
            if (! $setting->logo_path || ! Storage::disk('public')->exists($setting->logo_path)) {
                return redirect()->route('settings.index')->withErrors(['error' => 'Belum ada logo madrasah yang diunggah untuk dikonversi menjadi favicon.']);
            }

            $logoFullPath = Storage::disk('public')->path($setting->logo_path);
            $newFaviconPath = FaviconService::generateFavicon($logoFullPath);

            if ($newFaviconPath) {
                if ($setting->favicon_path && $setting->favicon_path !== $newFaviconPath && Storage::disk('public')->exists($setting->favicon_path)) {
                    Storage::disk('public')->delete($setting->favicon_path);
                }

                $setting->update(['favicon_path' => $newFaviconPath]);
                SchoolSetting::clearCache();

                return redirect()->route('settings.index')->with('success', 'Logo madrasah berhasil dikonversi menjadi favicon browser.');
            }

            return redirect()->route('settings.index')->withErrors(['error' => 'Gagal mengonversi logo menjadi favicon. Pastikan ekstensi GD aktif.']);
        } catch (\Throwable $e) {
            Log::error('convertLogoToFavicon failed: '.$e->getMessage());

            return redirect()->route('settings.index')->withErrors(['error' => 'Terjadi kesalahan: '.$e->getMessage()]);
        }
    }
}
