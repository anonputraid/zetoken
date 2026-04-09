# Zetoken

Zetoken adalah library PHP untuk membuat token sederhana yang diproses secara cepat melalui native C++.

---

## ⚠️ Peringatan Keamanan & Batasan Penggunaan

Zetoken dirancang untuk penggunaan umum (**General Purpose Token**).

**TIDAK COCOK** untuk:

- Menyimpan data sangat sensitif (perbankan, kartu kredit)
- Password (kata sandi)
- Data keuangan penting lainnya

**SANGAT COCOK** untuk:

- Token jawaban kuis atau ujian online
- Token tiket atau voucher akses sementara
- Obfuscation (menyamarkan ID atau parameter URL)
- Kebutuhan aplikasi non-keuangan lainnya dengan pembuatan token massal

---

## 🚀 Fitur Utama

- **Enkripsi**  
  Mengubah data teks menjadi token angka unik

- **Dekripsi**  
  Mengembalikan token angka menjadi data teks asli secara akurat

- **Keamanan**  
  Menggunakan:
  - `keyId` (identifier / offset)
  - `secretKey` (kunci utama)

  Sehingga token hanya dapat dibaca oleh pihak yang memiliki kunci yang sama

---

## ⚠️ Kelemahan & Limitasi

Harap diperhatikan bahwa Zetoken memiliki beberapa batasan teknis:

- **Pola Karakter**  
  Jumlah karakter asli dapat diprediksi dari panjang token

- **Standar Kriptografi**  
  Menggunakan custom rolling cipher  
  ❌ Tidak mengikuti standar seperti:
  - AES
  - RSA
  - ChaCha20

- **Tujuan Penggunaan**  
  ✔ Untuk kebutuhan fungsional aplikasi  
  ❌ Bukan untuk keamanan tingkat tinggi

---

## ⚠️ PERINGATAN: WAJIB KONFIGURASI ENV

Library ini **TIDAK AKAN BERFUNGSI** jika Anda tidak menentukan kunci keamanan.

Zetoken **tidak memiliki kunci cadangan** demi alasan keamanan. Anda **WAJIB** menyertakan konfigurasi berikut di dalam file `.env` proyek Anda:

```env
ZETOKEN_ACCESS_KEY_ID="identitas_unik_anda"
ZETOKEN_SECRET_KEY="kunci_rahasia_anda"
```

Jika kunci tidak ditemukan di ENV atau parameter fungsi, maka:

- Semua proses **enkripsi** akan gagal
- Semua proses **dekripsi** akan gagal
- Fungsi akan langsung mengembalikan nilai: `false`

---

## 🛠️ Alat Generator

Gunakan alat bantu berikut untuk membuat atau membaca token konfigurasi:

👉 [**BUKA ZETOKEN GENERATOR**](https://anonputraid.github.io/zetoken.html)

---

## 🧪 Hasil Uji Stress (100.000 Iterasi)

```
==================================================
MEMULAI ULTIMATE STRESS TEST: 100000 ITERASI
==================================================

Hasil Akhir:
- Total Waktu Eksekusi : 24.52 detik
- Rata-rata Enkripsi   : 0.11650 ms
- Rata-rata Dekripsi   : 0.11585 ms
- Latensi Terburuk     : 31.8429 ms
- Total Kegagalan      : 0
- Delta Memori PHP     : 0.95 KB
- STATUS               : [ LAYAK PRODUKSI - SANGAT STABIL ]

==================================================
```

---

## ⚙️ Persyaratan Sistem

Library ini menggunakan **PHP FFI** untuk berkomunikasi dengan core C++.

Pastikan sistem Anda memenuhi:

- PHP >= 7.4
- Ekstensi FFI aktif di `php.ini`:

```ini
extension=ffi
ffi.enable=true
```

> Setelah mengubah `php.ini`, pastikan untuk me-restart Web Server atau PHP-FPM.

---

## 📦 Instalasi

Gunakan Composer:

```bash
composer require zetwypro/zetoken
```

> ⚠️ Catatan: Library tidak akan berfungsi jika ekstensi FFI belum diaktifkan.

---

## 💻 Cara Penggunaan

### 1. Penggunaan Standar (Otomatis dari ENV)

Metode ini paling simpel karena otomatis mengambil kunci dari `.env`.

```php
use Zetwypro\Zetoken\Zetoken;

$zetoken = new Zetoken();

// Encode menggunakan KeyID & Secret dari .env
$token = $zetoken->encode("Pesan Rahasia");

// Decode menggunakan KeyID & Secret dari .env
$asli = $zetoken->decode($token);
```

---

### 2. Fitur Sign & VerifySign (Manual KeyID)

Gunakan fitur ini jika Anda ingin `keyId` dinamis (misal: ID User) tetapi `secretKey` tetap dari `.env`.

```php
$userId = "USER-9921";
$data = "Lulus Ujian";

// SIGN: Mengunci token khusus untuk User ID tersebut
$token = $zetoken->sign($data, $userId);

// VERIFY: Hanya bisa dibuka jika User ID-nya sama
$hasil = $zetoken->verifySign($token, $userId);

if ($hasil === false) {
    echo "Token tidak valid atau KeyID salah!";
}
```

---

## 📄 Lisensi

MIT License  
Dibuat oleh **Anonputraid**
