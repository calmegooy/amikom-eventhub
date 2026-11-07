<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrganizerApprovedMail;
use App\Mail\OrganizerRejectedMail;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrganizerController extends Controller
{
    /**
     * Menampilkan daftar organizer yang menunggu persetujuan & yang sudah aktif
     */
    public function index()
    {
        $pendingOrganizers = User::where('role', User::ROLE_ORGANIZER)
            ->where('is_approved', false)
            ->with('ownedOrganizations')
            ->latest()
            ->get();

        $approvedOrganizers = User::where('role', User::ROLE_ORGANIZER)
            ->where('is_approved', true)
            ->with('ownedOrganizations')
            ->latest()
            ->get();

        return view('admin.organizers.index', compact('pendingOrganizers', 'approvedOrganizers'));
    }

    /**
     * Menyetujui pendaftaran organizer
     */
    public function approve(User $organizer)
    {
        abort_unless($organizer->isOrganizer(), 404);

        DB::transaction(function () use ($organizer) {
            $organizer->update(['is_approved' => true]);
            $organization = $this->organizationFor($organizer);
            $organization->update([
                'status' => Organization::STATUS_APPROVED,
                'approved_at' => now(),
                'verified_at' => now(),
                'approved_by' => auth()->id(),
            ]);
        });

        Mail::to($organizer->email)->send(new OrganizerApprovedMail($organizer));

        return redirect()->back()->with('success', 'Akun organizer "'.$organizer->name.'" berhasil disetujui!');
    }

    /**
     * Menolak / Menutup akses organizer
     */
    public function reject(User $organizer)
    {
        abort_unless($organizer->isOrganizer(), 404);

        DB::transaction(function () use ($organizer) {
            $organizer->update(['is_approved' => false]);
            $organization = $this->organizationFor($organizer);
            $organization->update([
                'status' => Organization::STATUS_REJECTED,
                'approved_at' => null,
                'verified_at' => null,
                'approved_by' => null,
            ]);
        });

        Mail::to($organizer->email)->send(new OrganizerRejectedMail($organizer));

        return redirect()->back()->with('success', 'Status organizer "'.$organizer->name.'" telah dinonaktifkan.');
    }

    private function organizationFor(User $organizer): Organization
    {
        $organization = $organizer->ownedOrganizations()->first();
        if ($organization) {
            return $organization;
        }

        $baseSlug = $organizer->organization_slug
            ?: Str::slug($organizer->organization_name ?: $organizer->name);
        $slug = $baseSlug ?: 'organization-'.$organizer->id;

        if (Organization::where('slug', $slug)->exists()) {
            $slug .= '-'.$organizer->id;
        }

        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => $organizer->organization_name ?: $organizer->name,
            'slug' => $slug,
            'description' => $organizer->organization_bio,
            'bio' => $organizer->organization_bio,
            'email' => $organizer->email,
            'status' => $organizer->is_approved
                ? Organization::STATUS_APPROVED
                : Organization::STATUS_PENDING,
        ]);
        $organization->members()->syncWithoutDetaching([
            $organizer->id => ['role' => 'owner'],
        ]);

        return $organization;
    }
}
