<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class OtpService
{
    public function generateForContract(int $contratoId, int $ttlSeconds = 20): array
    {
        $otp = random_int(100000, 999999);
        Cache::put($this->cacheKey($contratoId), $otp, now()->addSeconds($ttlSeconds));

        return [
            'otp_formatted' => substr($otp, 0, 3) . ' ' . substr($otp, 3, 3),
            'otp_raw' => (string) $otp,
            'expires_in' => $ttlSeconds,
        ];
    }

    public function validate(int $contratoId, string $otpRaw): bool
    {
        $cache = Cache::get($this->cacheKey($contratoId));
        if (!$cache)
            return false;

        return ((string) $cache) === preg_replace('/\s+/', '', $otpRaw);
    }

    private function cacheKey(int $contratoId): string
    {
        return "otp_pago_{$contratoId}";
    }

    public function clear(int $contratoId): void
    {
        Cache::forget($this->cacheKey($contratoId));
    }
}
