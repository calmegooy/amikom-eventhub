<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Throwable;

class OrganizationController extends Controller
{
    public function edit()
    {
        $organization = $this->organization();
        Gate::authorize('update', $organization);

        return view('organizer.organization.edit', compact('organization'));
    }

    public function update(UpdateOrganizationRequest $request)
    {
        $organization = $this->organization();
        $validated = $request->validated();
        $oldLogoPath = $organization->logo_path;
        $newLogoPath = $request->hasFile('logo')
            ? $request->file('logo')->store('organizations', 'public')
            : null;

        try {
            DB::transaction(function () use ($organization, $validated, $newLogoPath): void {
                $organization->update([
                    'name' => $validated['name'],
                    'slug' => $validated['slug'],
                    'description' => $validated['description'] ?? null,
                    'bio' => $validated['description'] ?? null,
                    'logo_path' => $newLogoPath ?: $organization->logo_path,
                    'email' => $validated['email'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                ]);

                $organization->owner?->update([
                    'organization_name' => $validated['name'],
                    'organization_slug' => $validated['slug'],
                    'organization_bio' => $validated['description'] ?? null,
                ]);
            });
        } catch (Throwable $exception) {
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $exception;
        }

        if ($newLogoPath && $oldLogoPath) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        return redirect()
            ->route('organizer.organization.edit')
            ->with('success', 'Profil organisasi berhasil diperbarui.');
    }

    private function organization(): Organization
    {
        return request()->user()->editableOrganization()
            ?? abort(404, 'Organisasi tidak ditemukan.');
    }
}
