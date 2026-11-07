<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CheckInController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $this->authorizeEvent($request, $event);

        return view('organizer.scanner', compact('event'));
    }

    public function process(Request $request, Event $event)
    {
        $this->authorizeEvent($request, $event);

        $validated = $request->validate([
            'ticket_token' => ['required', 'string', 'max:128'],
        ]);

        $result = DB::transaction(function () use ($validated, $event, $request) {
            $ticket = Ticket::with(['transaction', 'event'])
                ->where('event_id', $event->id)
                ->where(function ($query) use ($validated) {
                    $query->where('qr_token', $validated['ticket_token'])
                        ->orWhere('code', $validated['ticket_token']);
                })
                ->lockForUpdate()
                ->first();

            if (! $ticket) {
                return [
                    'status' => 404,
                    'body' => [
                        'success' => false,
                        'message' => 'Tiket tidak ditemukan atau bukan milik event ini.',
                    ],
                ];
            }

            if (! $ticket->transaction->isPaid()) {
                return [
                    'status' => 422,
                    'body' => [
                        'success' => false,
                        'message' => 'Pembayaran tiket belum lunas.',
                    ],
                ];
            }

            if ($ticket->isCancelled()) {
                return [
                    'status' => 422,
                    'body' => [
                        'success' => false,
                        'message' => 'Tiket telah dibatalkan dan tidak dapat digunakan.',
                    ],
                ];
            }

            if ($ticket->isUsed()) {
                return [
                    'status' => 409,
                    'body' => [
                        'success' => false,
                        'message' => 'Tiket sudah pernah digunakan.',
                        'customer_name' => $ticket->holder_name,
                        'used_at' => $ticket->checked_in_at?->format('d M Y, H:i'),
                    ],
                ];
            }

            $ticket->update([
                'status' => Ticket::STATUS_USED,
                'checked_in_at' => now(),
                'checked_in_by' => $request->user()->id,
            ]);

            $hasUnusedTicket = Ticket::query()
                ->where('transaction_id', $ticket->transaction_id)
                ->where('status', Ticket::STATUS_ISSUED)
                ->exists();

            if (! $hasUnusedTicket) {
                $ticket->transaction->update(['ticket_status' => 'used']);

                DB::afterCommit(function () use ($ticket) {
                    \App\Jobs\SendCertificateJob::dispatch($ticket->transaction);
                });
            }

            return [
                'status' => 200,
                'body' => [
                    'success' => true,
                    'message' => 'Check-in berhasil.',
                    'customer_name' => $ticket->holder_name,
                    'ticket_code' => $ticket->code,
                    'event_title' => $event->title,
                ],
            ];
        }, 3);

        return response()->json($result['body'], $result['status']);
    }

    private function authorizeEvent(Request $request, Event $event): void
    {
        Gate::forUser($request->user())->authorize('scan', $event);
    }
}
