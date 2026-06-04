# Zetoken

Zetoken adalah library PHP untuk membuat token sederhana

---

## ⚠️ Peringatan Keamanan & Batasan Penggunaan

Zetoken dirancang untuk kebutuhan pribadi pada proyek saya, yaitu untuk melakukan hashing pada ID yang saling terhubung antar chat (**General Purpose Token**).

Namun, demi mematuhi standar kepatuhan siber global "Don't roll your own crypto", saya dengan ini menyatakan bahwa:

**TIDAK COCOK** untuk:
- Menyimpan data sangat sensitif (infrastruktur perbankan, kartu kredit, rekam medis)
- Hashing Password (kata sandi utama)
- Sistem keuangan kritikal berskala nasional

**SANGAT COCOK** untuk:
- Token jawaban kuis atau ujian online
- Token tiket atau voucher akses sementara
- Obfuscation (menyamarkan ID atau parameter URL secara aman)
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

- Zetoken belum diuji oleh pakar keamanan profesional. Oleh karena itu, untuk mematuhi standar keamanan global, Zetoken belum layak digunakan pada skala finansial, medis, atau infrastruktur kritis.

- Usia algoritma yang masih terlalu dini berpotensi menimbulkan celah keamanan zero-day. Oleh karena itu, untuk saat ini Zetoken hanya bisa digunakan untuk hashing pada hal-hal yang bersifat non-risiko atau berisiko rendah.

---

## ⚠️ PERINGATAN: WAJIB KONFIGURASI ENV

Library ini **TIDAK AKAN BERFUNGSI** jika Anda tidak menentukan kunci keamanan.

Zetoken **tidak memiliki kunci cadangan** demi alasan keamanan. Anda **WAJIB** menyertakan konfigurasi berikut di dalam file `.env` proyek Anda:

```env
ZETOKEN_ACCESS_KEY_ID="identitas_unik_anda"
ZETOKEN_SECRET_KEY="kunci_rahasia_anda"
ZETOKEN_ITERATIONS=1000
```

Jika kunci tidak ditemukan di ENV atau parameter fungsi, maka:

* Semua proses **enkripsi** akan gagal
* Semua proses **dekripsi** akan gagal
* Fungsi akan langsung mengembalikan nilai: `false`

---

## 🛠️ Alat Generator

Gunakan alat bantu berikut untuk membuat komponen konfigurasi kriptografi yang resmi dari kami:

👉 **[BUKA ZETOKEN GENERATOR](https://anonputraid.github.io/zetoken.html)**

---

## 🧪 Hasil Uji Stress (100.000 Iterasi)

```text
==================================================
MEMULAI ULTIMATE STRESS TEST: 100000 ITERASI (PURE PHP)
==================================================     

Hasil Akhir:
- Total Waktu Eksekusi : 732.71 detik
- Rata-rata Enkripsi   : 3.66690 ms
- Rata-rata Dekripsi   : 3.65769 ms
- Latensi Terburuk     : 132.3242 ms
- Total Kegagalan      : 0
- Delta Memori PHP     : 0.39 KB

==================================================
```

---

## ⚙️ Persyaratan Sistem

Pastikan server atau sistem Anda memenuhi standar modern berikut:

* **PHP >= 8.2** (Wajib untuk dukungan ekstensi `\Random\Randomizer` dan *engine* PCG64).
* Ekstensi PHP `openssl` harus diaktifkan.

---

## 📦 Instalasi

Gunakan Composer:

```bash
composer require zetwypro/zetoken
```

---

## 💻 Cara Penggunaan

### 1. Penggunaan Standar (Otomatis dari ENV)

Metode ini paling simpel karena otomatis mengambil kunci dari `.env`.

```php
use Zetwypro\Zetoken\Zetoken;

$zetoken = new Zetoken();

// Encode menggunakan KeyID, Secret, & Iterasi dari .env
$token = $zetoken->encode("Pesan Rahasia");

// Decode dan kembalikan ke teks asli secara utuh
$asli = $zetoken->decode($token);
```

---

### 2. Fitur Sign & VerifySign (Keamanan 3-Lapis / Manual KeyID)

Gunakan fitur ini jika Anda ingin mengikat token secara eksklusif ke suatu entitas (misal: ID User, Nomor Transaksi). Meskipun kuncinya diretas, token `User A` tidak akan bisa digunakan oleh `User B`.

```php
$userId = "USER-9921";
$data = "Lulus Ujian";

// SIGN: Mengunci token menggunakan kombinasi Master Access Key + $userId + Master Secret Key
$token = $zetoken->sign($data, $userId);

// VERIFY: Token hanya bisa dibuka dan diverifikasi integritasnya jika User ID-nya sama persis
$hasil = $zetoken->verifySign($token, $userId);

if ($hasil === false) {
    echo "Token palsu, termanipulasi, atau KeyID salah!";
}
```

---

## 📄 Lisensi

MIT License
Dibuat oleh **Anonputraid**

