<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Review;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    public function create($order_id)
    {
        $transaction = Transaction::with(['event.organizer', 'review'])
            ->where('order_id', $order_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (! $transaction->isPaid()) {
            abort(403, 'Tiket ini belum lunas.');
        }

        $existingReview = Review::query()
            ->where('user_id', Auth::id())
            ->where('event_id', $transaction->event_id)
            ->first();

        if ($existingReview) {
            $transaction->setRelation('review', $existingReview);

            return view('reviews.already-reviewed', compact('transaction'));
        }

        return view('reviews.create', compact('transaction'));
    }

    public function store(Request $request, $order_id)
    {
        $transaction = Transaction::with(['event', 'review'])
            ->where('order_id', $order_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Rating wajib diisi.',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
        ]);

        if (Review::where('user_id', Auth::id())->where('event_id', $transaction->event_id)->exists()) {
            return redirect()->route('review.create', $order_id)
                ->withErrors(['rating' => 'Anda sudah memberikan ulasan untuk event ini.']);
        }

        Gate::authorize('createForTransaction', [Review::class, $transaction]);

        try {
            Review::create([
                'transaction_id' => $transaction->id,
                'event_id' => $transaction->event_id,
                'organization_id' => $transaction->organization_id,
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => Review::STATUS_PUBLISHED,
            ]);
        } catch (QueryException $exception) {
            if (! $this->isDuplicateReviewViolation($exception)) {
                throw $exception;
            }

            return redirect()->route('review.create', $order_id)
                ->with('info', 'Ulasan untuk event ini sudah tercatat.');
        }

        return redirect()->route('review.create', $order_id)->with('success', 'Terima kasih atas ulasan Anda!');
    }

    /** Admin: semua ulasan seluruh event */
    public function adminIndex(Request $request)
    {
        $reviews = Review::with(['user', 'event', 'event.organization'])
            ->latest()
            ->paginate(20);

        $totalReviews = Review::count();
        $avgRating = Review::avg('rating');

        return view('admin.reviews.index', compact('reviews', 'totalReviews', 'avgRating'));
    }

    /** Admin: hapus ulasan */
    public function adminDestroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Ulasan berhasil dihapus.');
    }

    /** Organizer: ulasan untuk event miliknya */
    public function organizerIndex(Request $request)
    {
        $user = Auth::user();
        $organizationIds = $user->approvedOrganizationIds();

        $reviews = Review::with(['user', 'event'])
            ->whereHas('event', function ($q) use ($organizationIds, $user) {
                $q->where(function ($inner) use ($organizationIds, $user) {
                    if ($organizationIds->isNotEmpty()) {
                        $inner->whereIn('organization_id', $organizationIds);
                    }
                    $inner->orWhere('organizer_id', $user->id);
                });
            })
            ->latest()
            ->paginate(20);

        $avgRating = $reviews->avg('rating');
        $totalReviews = $reviews->total();

        return view('organizer.reviews.index', compact('reviews', 'avgRating', 'totalReviews'));
    }

    private function isDuplicateReviewViolation(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['19', '23000'], true)
            && Str::contains($exception->getMessage(), [
                'reviews_user_event_unique',
                'reviews.user_id, reviews.event_id',
                'reviews_transaction_id_unique',
                'reviews.transaction_id',
            ]);
    }
}
