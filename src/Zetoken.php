<?php

namespace Zetwypro\Zetoken;

use FFI;
use Exception;

class Zetoken
{
    /**
     * Objek FFI instance
     */
    private $ffi;

    /**
     * Inisialisasi library
     * @param string|null $libPath Jalur kustom ke file .so atau .dll
     */
    public function __construct(?string $libPath = null)
    {
        if (!extension_loaded('ffi')) {
            throw new Exception("Ekstensi PHP FFI tidak aktif.");
        }

        if ($libPath === null) {
            $ext = (PHP_OS_FAMILY === 'Windows') ? 'dll' : 'so';
            $libPath = __DIR__ . DIRECTORY_SEPARATOR . "gabung.{$ext}";

            if (!file_exists($libPath)) {
                $libPath = getcwd() . DIRECTORY_SEPARATOR . "gabung.{$ext}";
            }
        }

        if (!file_exists($libPath)) {
            throw new Exception("Binary library C++ tidak ditemukan.");
        }

        try {
            $this->ffi = FFI::cdef("
                const char* encrypt_cipher(const char* start_point, const char* seed, const char* input_text);
                const char* decrypt_cipher(const char* start_point, const char* seed, const char* cipher_text);
            ", $libPath);
        } catch (Exception $e) {
            throw new Exception("Gagal inisialisasi FFI: " . $e->getMessage());
        }
    }

    /**
     * Helper untuk mendapatkan KeyID dan SecretKey secara berjenjang:
     * 1. Dari parameter fungsi
     * 2. Dari Environment Variable (ENV)
     */
    private function resolveKeys(?string $keyId, ?string $secretKey): array
    {
        $finalKeyId = $keyId ?: (getenv('ZETOKEN_ACCESS_KEY_ID') ?: ($_ENV['ZETOKEN_ACCESS_KEY_ID'] ?? null));
        $finalSecret = $secretKey ?: (getenv('ZETOKEN_SECRET_KEY') ?: ($_ENV['ZETOKEN_SECRET_KEY'] ?? null));

        return [$finalKeyId, $finalSecret];
    }

    /**
     * ENCODE - Membuat token angka sederhana
     * @return string|bool Mengembalikan token atau false jika kunci tidak ada
     */
    public function encode(string $text, ?string $keyId = null, ?string $secretKey = null)
    {
        [$kid, $sec] = $this->resolveKeys($keyId, $secretKey);

        if (!$kid || !$sec) {
            return false;
        }

        return (string) $this->ffi->encrypt_cipher($kid, $sec, $text);
    }

    /**
     * DECODE - Membaca token angka kembali ke teks asli
     * @return string|bool Mengembalikan teks asli atau false jika kunci tidak ada
     */
    public function decode(string $cipherText, ?string $keyId = null, ?string $secretKey = null)
    {
        [$kid, $sec] = $this->resolveKeys($keyId, $secretKey);

        if (!$kid || !$sec) {
            return false;
        }

        return (string) $this->ffi->decrypt_cipher($kid, $sec, $cipherText);
    }

    /**
     * SIGN - Enkripsi yang mewajibkan KeyID
     */
    public function sign(string $text, string $keyId, ?string $secretKey = null)
    {
        [, $sec] = $this->resolveKeys(null, $secretKey);

        if (!$keyId || !$sec) {
            return false;
        }

        return (string) $this->ffi->encrypt_cipher($keyId, $sec, $text);
    }

    /**
     * VERIFY SIGN - Dekripsi yang mewajibkan KeyID
     */
    public function verifySign(string $token, string $keyId, ?string $secretKey = null)
    {
        [, $sec] = $this->resolveKeys(null, $secretKey);

        if (!$keyId || !$sec) {
            return false;
        }

        return (string) $this->ffi->decrypt_cipher($keyId, $sec, $token);
    }
}