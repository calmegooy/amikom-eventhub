<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Transaction;

class EventController extends Controller
{
    // Menampilkan halaman detail event — dinamis dari DB
    public function show($id)
    {
        $event = Event::query()
            ->publiclyVisible()
            ->with(['category', 'organizer', 'organization'])
            ->findOrFail($id);

        return view('event-detail', compact('event'));
    }

    // Menampilkan halaman e-ticket
    public function ticket($order_id)
    {
        $transaction = Transaction::with('event')->where('order_id', $order_id)->firstOrFail();

        return view('ticket', compact('transaction'));
    }
}
