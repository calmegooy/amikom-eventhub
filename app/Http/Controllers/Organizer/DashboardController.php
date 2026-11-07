<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Review;
use App\Models\Ticket;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $organizer = Auth::user();
        $eventsQuery = Event::query()->managedBy($organizer);
        $paidTransactions = Transaction::query()
            ->managedBy($organizer)
            ->paid();

        $totalEvents = (clone $eventsQuery)->count();
        $totalRevenue = (clone $paidTransactions)->sum('total_price');
        $totalTicketsSold = (clone $paidTransactions)->sum('quantity');
        $totalCheckedIn = Ticket::query()
            ->managedBy($organizer)
            ->where('status', Ticket::STATUS_USED)
            ->count();

        $upcomingEvents = (clone $eventsQuery)->where('date', '>=', now())->count();
        $pendingOrders = Transaction::query()
            ->managedBy($organizer)
            ->where('payment_status', Transaction::PAYMENT_PENDING)
            ->count();

        $avgRating = Review::query()
            ->managedBy($organizer)
            ->published()
            ->avg('rating');
        $avgRating = $avgRating ? round($avgRating, 1) : null;

        $monthlyRevenue = (clone $paidTransactions)
            ->where('created_at', '>=', now()->subMonths(6))
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($transaction) => $transaction->created_at->format('M Y'))
            ->map(fn ($group, $month) => (object) [
                'month' => $month,
                'total' => $group->sum('total_price'),
            ])
            ->values();

        $recentTransactions = Transaction::with('event')
            ->managedBy($organizer)
            ->latest()
            ->take(5)
            ->get();

        $events = (clone $eventsQuery)
            ->withSum([
                'transactions as total_tickets_sold' => fn ($query) => $query->paid(),
            ], 'quantity')
            ->latest()
            ->get();

        // Alias lama dipertahankan agar test dan komponen dashboard lama tetap kompatibel.
        $ticketsSold = $totalTicketsSold;
        $eventsWithTicketsSold = $events;

        return view('organizer.dashboard', compact(
            'totalEvents',
            'totalRevenue',
            'totalTicketsSold',
            'totalCheckedIn',
            'upcomingEvents',
            'pendingOrders',
            'avgRating',
            'monthlyRevenue',
            'recentTransactions',
            'events',
            'ticketsSold',
            'eventsWithTicketsSold',
        ));
    }
}
