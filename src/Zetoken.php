<?php

namespace Zetwypro\Zetoken;

use Exception;

class Zetoken
{
    public function __construct()
    {
        if (PHP_VERSION_ID < 80200) {
            throw new Exception("Zetoken requires PHP 8.2+ for modern cryptographic support.");
        }
    }

    private function resolveKeys(?string $keyId, ?string $secretKey): array
    {
        $finalKeyId = $keyId ?: (getenv('ZETOKEN_ACCESS_KEY_ID') ?: ($_ENV['ZETOKEN_ACCESS_KEY_ID'] ?? null));
        $finalSecret = $secretKey ?: (getenv('ZETOKEN_SECRET_KEY') ?: ($_ENV['ZETOKEN_SECRET_KEY'] ?? null));
        
        $finalIterations = (int)(getenv('ZETOKEN_ITERATIONS') ?: ($_ENV['ZETOKEN_ITERATIONS'] ?? 1000));
        
        if ($finalIterations < 1) {
            $finalIterations = 1000;
        }

        return [$finalKeyId, $finalSecret, $finalIterations];
    }

    private function deriveCryptographicKey(string $startPoint, string $seed, int $iterations): string
    {
        return hash_pbkdf2('sha512', $seed, $startPoint, $iterations, 16, true);
    }

    public function encode(string $text, ?string $keyId = null, ?string $secretKey = null, ?int $ttl = null): string|false
    {
        [$kid, $sec, $iterations] = $this->resolveKeys($keyId, $secretKey);

        if (!$kid || !$sec) {
            return false;
        }

        if ($ttl !== null && $ttl > 0) {
            $expTime = time() + $ttl;
            $text = $text . '__ZTX__' . $expTime;
        }

        $aesKey = $this->deriveCryptographicKey($kid, $sec, $iterations);

        $iv = random_bytes(12);

        $tag = "";
        $cipherText = openssl_encrypt($text, 'aes-128-gcm', $aesKey, OPENSSL_RAW_DATA, $iv, $tag);

        if ($cipherText === false) {
            return false;
        }

        $payload = $iv . $tag . $cipherText;

        $numericResult = "";
        $len = strlen($payload);
        for ($i = 0; $i < $len; $i++) {
            $numericResult .= str_pad((string)ord($payload[$i]), 3, '0', STR_PAD_LEFT);
        }

        return $numericResult;
    }

    public function decode(string $cipherText, ?string $keyId = null, ?string $secretKey = null, int $leeway = 60): string|false
    {
        [$kid, $sec, $iterations] = $this->resolveKeys($keyId, $secretKey);

        if (!$kid || !$sec) {
            return false;
        }

        $len = strlen($cipherText);
        if ($len % 3 !== 0 || !ctype_digit($cipherText)) {
            return false;
        }

        $decodedBytes = "";
        for ($i = 0; $i < $len; $i += 3) {
            $chunk = substr($cipherText, $i, 3);
            $decodedBytes .= chr((int)$chunk);
        }

        if (strlen($decodedBytes) < 28) {
            return false;
        }

        $iv = substr($decodedBytes, 0, 12);
        $tag = substr($decodedBytes, 12, 16);
        $actualCipherText = substr($decodedBytes, 28);

        $aesKey = $this->deriveCryptographicKey($kid, $sec, $iterations);

        $decryptedText = openssl_decrypt($actualCipherText, 'aes-128-gcm', $aesKey, OPENSSL_RAW_DATA, $iv, $tag);

        if ($decryptedText === false) {
            return false;
        }

        $pos = strrpos($decryptedText, '__ZTX__');
        
        if ($pos !== false) {
            $expString = substr($decryptedText, $pos + 7); 
            
            if ($expString !== '' && ctype_digit($expString)) {
                $expTime = (int)$expString;
                
                if ((time() - $leeway) > $expTime) {
                    return false; // Token hangus / Expired
                }
                
                return substr($decryptedText, 0, $pos);
            }
        }

        return $decryptedText;
    }

    public function sign(string $text, string $keyId, ?string $secretKey = null, ?int $ttl = null): string|false
    {
        [$masterAccessKey, $masterSecretKey] = $this->resolveKeys(null, $secretKey);

        if (!$masterAccessKey || !$masterSecretKey || empty($keyId)) {
            return false;
        }

        $layeredKeyId = $masterAccessKey . '::' . $keyId;

        return $this->encode($text, $layeredKeyId, $masterSecretKey, $ttl);
    }

    public function verifySign(string $token, string $keyId, ?string $secretKey = null, int $leeway = 60): string|false
    {
        [$masterAccessKey, $masterSecretKey] = $this->resolveKeys(null, $secretKey);

        if (!$masterAccessKey || !$masterSecretKey || empty($keyId)) {
            return false;
        }

        $layeredKeyId = $masterAccessKey . '::' . $keyId;

        return $this->decode($token, $layeredKeyId, $masterSecretKey, $leeway);
    }
}