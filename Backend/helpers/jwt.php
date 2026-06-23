<?php
// backend/helpers/jwt.php
class JWT {
    private static $secret = 'ClaveSecreta_EstateArch_2026';

    public static function generar($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function verificar($token) {
        $partes = explode('.', $token);
        if (count($partes) !== 3) return false;
        list($header, $payload, $signature) = $partes;
        $validSignature = hash_hmac('sha256', $header . "." . $payload, self::$secret, true);
        $validSignatureB64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($validSignature));
        if ($signature === $validSignatureB64) {
            return json_decode(base64_decode($payload), true);
        }
        return false;
    }
}