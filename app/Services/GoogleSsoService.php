<?php

namespace App\Services;

use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleSsoService
{
    public function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }

    public function resolveCustomer(SocialiteUser $googleUser): User
    {
        $googleId = trim((string) $googleUser->getId());
        $email = strtolower(trim((string) $googleUser->getEmail()));
        $emailVerified = filter_var(
            $googleUser->user['email_verified'] ?? $googleUser->user['verified_email'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        if (
            ! $emailVerified
            || $googleId === ''
            || mb_strlen($googleId) > 255
            || ! filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            throw new DomainException('Email Google harus terverifikasi.');
        }

        return DB::transaction(function () use ($googleUser, $googleId, $email): User {
            $linkedUser = User::query()
                ->where('google_id', $googleId)
                ->lockForUpdate()
                ->first();

            if ($linkedUser) {
                $this->assertCustomer($linkedUser);

                $emailOwnerExists = User::query()
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->where('id', '!=', $linkedUser->id)
                    ->exists();

                if ($emailOwnerExists) {
                    throw new DomainException('Email Google sudah digunakan oleh akun lain.');
                }

                $linkedUser->update([
                    'name' => $this->providerName($googleUser, $email),
                    'email' => $email,
                    'avatar' => $this->providerAvatar($googleUser),
                    'email_verified_at' => $linkedUser->email_verified_at ?: now(),
                ]);

                return $linkedUser->fresh();
            }

            $emailUser = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->lockForUpdate()
                ->first();

            if ($emailUser) {
                $this->assertCustomer($emailUser);

                if (filled($emailUser->google_id) && $emailUser->google_id !== $googleId) {
                    throw new DomainException('Akun ini sudah terhubung dengan akun Google lain.');
                }

                $emailUser->update([
                    'google_id' => $googleId,
                    'avatar' => $this->providerAvatar($googleUser),
                    'email_verified_at' => $emailUser->email_verified_at ?: now(),
                ]);

                return $emailUser->fresh();
            }

            return User::create([
                'name' => $this->providerName($googleUser, $email),
                'email' => $email,
                'email_verified_at' => now(),
                'google_id' => $googleId,
                'avatar' => $this->providerAvatar($googleUser),
                'password' => Str::password(32),
                'role' => User::ROLE_CUSTOMER,
            ]);
        }, 3);
    }

    private function assertCustomer(User $user): void
    {
        if (! $user->isCustomer()) {
            throw new DomainException(
                'Akun admin/organizer tidak dapat masuk melalui Google SSO pelanggan.'
            );
        }
    }

    private function providerName(SocialiteUser $googleUser, string $email): string
    {
        return Str::limit(trim((string) $googleUser->getName()) ?: $email, 255, '');
    }

    private function providerAvatar(SocialiteUser $googleUser): ?string
    {
        $avatar = trim((string) $googleUser->getAvatar());

        return $avatar === '' ? null : Str::limit($avatar, 255, '');
    }
}
