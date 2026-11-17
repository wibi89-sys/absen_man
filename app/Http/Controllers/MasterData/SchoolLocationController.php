<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\SchoolLocation;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolLocationController extends Controller
{
    public function index(): View
    {
        $locations = SchoolLocation::orderBy('name')->paginate(15);

        return view('master.locations.index', compact('locations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location_type' => ['required', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_meters' => ['nullable', 'integer', 'min:10', 'max:5000'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $loc = SchoolLocation::create($validated);

        AuditService::log(
            event: 'location_created',
            auditableType: SchoolLocation::class,
            auditableId: $loc->id,
            newValues: $loc->toArray()
        );

        return redirect()->route('master.locations.index')->with('success', 'Lokasi sekolah/ibadah berhasil ditambahkan.');
    }

    public function update(Request $request, SchoolLocation $location): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location_type' => ['required', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_meters' => ['nullable', 'integer', 'min:10', 'max:5000'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $oldValues = $location->toArray();
        $location->update($validated);

        AuditService::log(
            event: 'location_updated',
            auditableType: SchoolLocation::class,
            auditableId: $location->id,
            oldValues: $oldValues,
            newValues: $location->toArray()
        );

        return redirect()->route('master.locations.index')->with('success', 'Data lokasi berhasil diperbarui.');
    }

    public function destroy(SchoolLocation $location): RedirectResponse
    {
        $oldValues = $location->toArray();
        $location->delete();

        AuditService::log(
            event: 'location_deleted',
            auditableType: SchoolLocation::class,
            auditableId: $location->id,
            oldValues: $oldValues
        );

        return redirect()->route('master.locations.index')->with('success', 'Data lokasi berhasil dihapus.');
    }
}
