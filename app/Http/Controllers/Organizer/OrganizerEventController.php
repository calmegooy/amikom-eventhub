use App\Models\Event;
use App\Models\Transaction;

/**
 * Export daftar peserta event ke format CSV
 */
public function exportAttendees(Event $event)
{
    // Pastikan event milik organizer yang sedang login
    if ($event->user_id !== auth()->id()) {
        abort(403, 'Akses tidak diizinkan.');
    }

    $fileName = 'peserta-event-' . \Str::slug($event->title) . '.csv';

    $transactions = Transaction::where('event_id', $event->id)
        ->where('payment_status', 'paid')
        ->latest()
        ->get();

    $headers = [
        "Content-type"        => "text/csv; charset=utf-8",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $columns = ['Order ID', 'Nama Lengkap', 'Email', 'No WhatsApp', 'Jumlah Tiket', 'Status Check-in', 'Waktu Pembayaran'];

    $callback = function() use($transactions, $columns) {
        $file = fopen('php://output', 'w');
        
        // Menambahkan BOM agar file CSV terbaca rapi dengan karakter UTF-8 di Excel
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($file, $columns);

        foreach ($transactions as $trx) {
            $row['Order ID']         = $trx->order_id;
            $row['Nama Lengkap']     = $trx->customer_name;
            $row['Email']            = $trx->customer_email;
            $row['No WhatsApp']      = $trx->customer_phone;
            $row['Jumlah Tiket']     = $trx->quantity;
            $row['Status Check-in']  = $trx->is_checked_in ? 'Sudah Check-in' : 'Belum Check-in';
            $row['Waktu Pembayaran'] = $trx->updated_at->format('Y-m-d H:i:s');

            fputcsv($file, [
                $row['Order ID'],
                $row['Nama Lengkap'],
                $row['Email'],
                $row['No WhatsApp'],
                $row['Jumlah Tiket'],
                $row['Status Check-in'],
                $row['Waktu Pembayaran']
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}