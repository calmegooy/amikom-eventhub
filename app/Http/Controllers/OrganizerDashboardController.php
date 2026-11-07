<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizerDashboardController extends Controller
{
    public function index()
    {
        $organizerId = Auth::id();

        // 1. Ambil semua event milik organizer ini
        $events = Event::where('organizer_id', $organizerId)
            ->withCount(['transactions as total_tickets_sold' => function ($query) {
                $query->whereIn('status', ['success', 'paid']);
            }])
            ->latest()
            ->get();

        // 2. Kalkulasi statistik agregat
        $eventIds = $events->pluck('id');

        $totalEvents = $events->count();
        
        $paidTransactions = Transaction::whereIn('event_id', $eventIds)
            ->whereIn('status', ['success', 'paid']);

        $totalRevenue = (clone $paidTransactions)->sum('total_price');
        $totalTicketsSold = (clone $paidTransactions)->sum('quantity');
        
        $totalCheckedIn = (clone $paidTransactions)
            ->where(function ($q) {
                $q->where('ticket_status', 'used')
                  ->orWhere('is_checked_in', true);
            })
            ->count();

        return view('organizer.dashboard', compact(
            'events',
            'totalEvents',
            'totalRevenue',
            'totalTicketsSold',
            'totalCheckedIn'
        ));
    }
}