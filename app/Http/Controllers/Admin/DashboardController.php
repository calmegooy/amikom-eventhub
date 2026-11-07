<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard admin
     */
    public function index()
    {
        $paidTransactions = Transaction::query()->paid();

        // Revenue dan tiket terjual hanya berasal dari status internal paid.
        $totalRevenue = (clone $paidTransactions)->sum('total_price');
        $ticketsSold = (clone $paidTransactions)->sum('quantity');

        $activeEvents = Event::where('date', '>=', now())->count();
        $pendingOrders = Transaction::where('payment_status', Transaction::PAYMENT_PENDING)->count();
        $totalUsers = User::count();
        $recentTransactions = Transaction::with('event')->latest()->take(5)->get();

        // Query grafik lintas SQLite (test) dan MySQL (aplikasi).
        $isSqlite = DB::getDriverName() === 'sqlite';
        $monthRaw = $isSqlite ? "strftime('%m', created_at)" : 'MONTH(created_at)';
        $yearRaw = $isSqlite ? "strftime('%Y', created_at)" : 'YEAR(created_at)';

        $revenueQuery = Transaction::query()
            ->paid()
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw("$monthRaw as month_num"),
                DB::raw("$yearRaw as year_num"),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->groupBy(DB::raw($monthRaw), DB::raw($yearRaw))
            ->get()
            ->keyBy(function ($item) {
                return intval($item->month_num).'-'.intval($item->year_num);
            });

        $eventsQuery = Event::where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw("$monthRaw as month_num"),
                DB::raw("$yearRaw as year_num"),
                DB::raw('COUNT(*) as total_events')
            )
            ->groupBy(DB::raw($monthRaw), DB::raw($yearRaw))
            ->get()
            ->keyBy(function ($item) {
                return intval($item->month_num).'-'.intval($item->year_num);
            });

        // Query pertumbuhan user baru per bulan (SQLite + MySQL compatible).
        $usersQuery = User::where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw("$monthRaw as month_num"),
                DB::raw("$yearRaw as year_num"),
                DB::raw('COUNT(*) as total_users')
            )
            ->groupBy(DB::raw($monthRaw), DB::raw($yearRaw))
            ->get()
            ->keyBy(function ($item) {
                return intval($item->month_num).'-'.intval($item->year_num);
            });

        $chartData = [
            'labels'  => [],
            'revenue' => [],
            'events'  => [],
            'users'   => [],
        ];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthKey  = intval($monthDate->format('m')).'-'.intval($monthDate->format('Y'));

            $chartData['labels'][]  = $monthDate->format('M Y');
            $chartData['revenue'][] = isset($revenueQuery[$monthKey]) ? (int) $revenueQuery[$monthKey]->total_revenue : 0;
            $chartData['events'][]  = isset($eventsQuery[$monthKey]) ? (int) $eventsQuery[$monthKey]->total_events : 0;
            $chartData['users'][]   = isset($usersQuery[$monthKey]) ? (int) $usersQuery[$monthKey]->total_users : 0;
        }

        return view('admin.dashboard', compact(
            'totalRevenue',
            'ticketsSold',
            'activeEvents',
            'pendingOrders',
            'totalUsers',
            'recentTransactions',
            'chartData'
        ));
    }
}
