<?php

namespace App\Http\Controllers;

use App\Models\Organization;

class OrganizerProfileController extends Controller
{
    public function show(string $slug)
    {
        $organization = Organization::query()
            ->where('slug', $slug)
            ->where('status', Organization::STATUS_APPROVED)
            ->with('owner')
            ->firstOrFail();

        $events = $organization->events()
            ->with('category')
            ->latest('date')
            ->get();

        $reviews = $organization->publishedReviews()
            ->with(['event', 'user', 'transaction'])
            ->latest()
            ->get();

        $avgRating = round((float) $reviews->avg('rating'), 1);
        $totalReviews = $reviews->count();

        return view('organizer-profile', compact(
            'organization',
            'events',
            'reviews',
            'avgRating',
            'totalReviews',
        ));
    }
}
