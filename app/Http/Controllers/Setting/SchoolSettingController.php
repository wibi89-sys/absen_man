<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SchoolSettingController extends Controller
{
    /**
     * Show school settings form.
     */
    public function index(): View
    {
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
            'enable_demo_mode' => ['nullable'],
            'gate_welcome_text' => ['nullable', 'string', 'max:255'],
        ], [
            'school_name.required' => 'Nama madrasah/sekolah wajib diisi.',
            'school_level.required' => 'Jenjang pendidikan wajib dipilih.',
            'principal_name.required' => 'Nama kepala madrasah wajib diisi.',
            'logo.image' => 'File logo harus berupa gambar (PNG, JPG, SVG, WebP).',
            'logo.max' => 'Ukuran file logo maksimal 2MB.',
        ]);

        $setting = SchoolSetting::current();

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $logoPath = $request->file('logo')->store('settings', 'public');
            $validated['logo_path'] = $logoPath;
        }

        // Handle Delete Logo Request
        if ($request->boolean('delete_logo') && $setting->logo_path) {
            if (Storage::disk('public')->exists($setting->logo_path)) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $validated['logo_path'] = null;
        }

        $validated['enable_demo_mode'] = $request->boolean('enable_demo_mode');

        unset($validated['logo']);

        $setting->update($validated);
        Cache::forget('school_setting');

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

        return redirect()->route('settings.index')->with('success', 'Profil dan identitas madrasah berhasil diperbarui.');
    }
}
