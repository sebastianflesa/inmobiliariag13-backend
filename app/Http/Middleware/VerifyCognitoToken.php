<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyCognitoToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();
        if (!$bearer) {
            return response()->json(['message' => 'Token no enviado.'], 401);
        }

        [$headerB64, $payloadB64, $signatureB64] = explode('.', $bearer) + [null, null, null];
        if (!$headerB64 || !$payloadB64 || !$signatureB64) {
            return response()->json(['message' => 'Token invalido.'], 401);
        }

        $header = json_decode($this->base64UrlDecode($headerB64), true);
        $payload = json_decode($this->base64UrlDecode($payloadB64), true);
        $signature = $this->base64UrlDecode($signatureB64);

        if (!$header || !$payload || !$signature || !isset($header['kid'])) {
            return response()->json(['message' => 'Token invalido.'], 401);
        }

        $region = config('services.cognito.region');
        $userPoolId = config('services.cognito.user_pool_id');
        $clientId = config('services.cognito.app_client_id');

        if (!$region || !$userPoolId || !$clientId) {
            return response()->json(['message' => 'Configuracion de Cognito incompleta.'], 500);
        }

        $issuer = sprintf('https://cognito-idp.%s.amazonaws.com/%s', $region, $userPoolId);

        if (($payload['iss'] ?? null) !== $issuer) {
            return response()->json(['message' => 'Issuer invalido.'], 401);
        }

        $aud = $payload['aud'] ?? $payload['client_id'] ?? null;
        if ($aud !== $clientId) {
            return response()->json(['message' => 'Audiencia invalida.'], 401);
        }

        if (($payload['token_use'] ?? null) !== 'access') {
            return response()->json(['message' => 'Token no autorizado.'], 401);
        }

        $jwks = $this->getJwks($issuer);
        $jwk = collect($jwks['keys'] ?? [])->firstWhere('kid', $header['kid']);
        if (!$jwk) {
            return response()->json(['message' => 'Token sin llave valida.'], 401);
        }

        $publicKey = $this->jwkToPem($jwk);
        $data = $headerB64 . '.' . $payloadB64;
        $verified = openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        if ($verified !== 1) {
            return response()->json(['message' => 'Firma invalida.'], 401);
        }

        return $next($request);
    }

    private function getJwks(string $issuer): array
    {
        $url = $issuer . '/.well-known/jwks.json';

        return Cache::remember('cognito_jwks_' . md5($url), 300, function () use ($url) {
            $response = Http::get($url);
            if (!$response->successful()) {
                return [];
            }

            return $response->json();
        });
    }

    private function jwkToPem(array $jwk): string
    {
        $modulus = $this->base64UrlDecode($jwk['n']);
        $exponent = $this->base64UrlDecode($jwk['e']);

        $modulus = "\x02" . $this->encodeLength(strlen($modulus)) . $modulus;
        $exponent = "\x02" . $this->encodeLength(strlen($exponent)) . $exponent;

        $rsaPublicKey = "\x30" . $this->encodeLength(strlen($modulus) + strlen($exponent)) . $modulus . $exponent;

        $rsaOid = "\x30\x0d\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";
        $rsaPublicKey = "\x30"
            . $this->encodeLength(strlen($rsaOid . "\x03" . $this->encodeLength(strlen($rsaPublicKey) + 1) . "\x00" . $rsaPublicKey))
            . $rsaOid
            . "\x03" . $this->encodeLength(strlen($rsaPublicKey) + 1) . "\x00" . $rsaPublicKey;

        $pem = "-----BEGIN PUBLIC KEY-----\r\n";
        $pem .= chunk_split(base64_encode($rsaPublicKey), 64, "\r\n");
        $pem .= "-----END PUBLIC KEY-----";

        return $pem;
    }

    private function encodeLength(int $length): string
    {
        if ($length <= 0x7f) {
            return chr($length);
        }

        $temp = ltrim(pack('N', $length), "\x00");
        return chr(0x80 | strlen($temp)) . $temp;
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }
}
