<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Services\GoogleSsoService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    // Halaman login publik (pembeli tiket / organizer)
    public function showLogin(GoogleSsoService $googleSso)
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('auth.user-login', [
            'googleSsoAvailable' => $googleSso->isConfigured(),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return $this->redirectByRole();
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    // Registrasi akun biasa (pembeli tiket)
    public function showRegister()
    {
        return view('auth.user-register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_CUSTOMER,
        ]);

        Auth::login($user);

        return redirect()->route('home');
    }

    // Registrasi akun Organizer / Kepanitiaan (Multi-Tenant)
    public function showRegisterOrganizer()
    {
        return view('auth.organizer-register');
    }

    public function registerOrganizer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'organization_name' => 'required|string|max:255',
            'organization_bio' => 'nullable|string|max:2000',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $slug = Str::slug($validated['organization_name']).'-'.strtolower(Str::random(6));

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => User::ROLE_ORGANIZER,
                'is_approved' => false,
                'organization_name' => $validated['organization_name'],
                'organization_slug' => $slug,
                'organization_bio' => $validated['organization_bio'] ?? null,
            ]);

            $organization = Organization::create([
                'owner_id' => $user->id,
                'name' => $validated['organization_name'],
                'slug' => $slug,
                'description' => $validated['organization_bio'] ?? null,
                'bio' => $validated['organization_bio'] ?? null,
                'email' => $validated['email'],
                'status' => Organization::STATUS_PENDING,
            ]);

            $organization->members()->attach($user->id, ['role' => 'owner']);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('organizer.waiting')
            ->with('info', 'Akun penyelenggara Anda berhasil dibuat dan menunggu persetujuan Superadmin sebelum bisa mempublikasikan event.');
    }

    // --- Login Instan via Google (Laravel Socialite) ---
    public function redirectToGoogle(GoogleSsoService $googleSso)
    {
        if (! $googleSso->isConfigured()) {
            return redirect()->route('user.login')->withErrors([
                'email' => 'Google SSO belum dikonfigurasi.',
            ]);
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request, GoogleSsoService $googleSso)
    {
        if ($request->filled('error')) {
            return redirect()->route('user.login')->withErrors([
                'email' => 'Autentikasi dengan Google dibatalkan atau gagal. Silakan coba lagi.',
            ]);
        }

        if (! $googleSso->isConfigured()) {
            return redirect()->route('user.login')->withErrors([
                'email' => 'Google SSO belum dikonfigurasi.',
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('user.login')->withErrors(['email' => 'Autentikasi dengan Google dibatalkan atau gagal. Silakan coba lagi.']);
        }

        try {
            $user = $googleSso->resolveCustomer($googleUser);
        } catch (DomainException $exception) {
            return redirect()->route('user.login')
                ->withErrors(['email' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('user.login')->withErrors([
                'email' => 'Autentikasi dengan Google gagal diproses. Silakan coba lagi.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectByRole();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    protected function redirectByRole()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isOrganizer()) {
            return redirect()->route('organizer.dashboard');
        }

        return redirect()->route('home');
    }
}
