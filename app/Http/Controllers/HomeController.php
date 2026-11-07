<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Partner;
use App\Models\Transaction;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $events = Event::query()
            ->publiclyVisible()
            ->with('category')
            ->orderBy('id', 'asc')
            ->take(6)
            ->get();
        $partners = Partner::all();
        $categories = Category::all();

        $totalEvents = Event::query()->publiclyVisible()->count();
        $totalParticipants = Transaction::query()->paid()->sum('quantity');
        $totalOrganizers = Organization::query()
            ->where('status', Organization::STATUS_APPROVED)
            ->count();

        return view('welcome', compact(
            'events',
            'partners',
            'categories',
            'totalEvents',
            'totalParticipants',
            'totalOrganizers'
        ));
    }

    public function profil()
    {
        return view('profil');
    }

    public function katalog(Request $request)
    {
        $categories = Category::all();

        $query = Event::query()
            ->publiclyVisible()
            ->with('category')
            ->orderBy('id', 'asc');

        if ($request->filled('q')) {
            $searchTerm = '%'.$request->q.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('location', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $events = $query->paginate(6);

        return view('katalog', compact('events', 'categories'));
    }

    public function bantuan()
    {
        return view('bantuan');
    }

    public function kontak()
    {
        return view('contact');
    }
}
