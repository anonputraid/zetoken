# Zetoken

Zetoken is a PHP library for generating simple tokens.

---

## ⚠️ Security Warning & Usage Limitations

Zetoken is specifically designed to meet the needs of my project. This library was developed to handle WebSocket handshake processes across different programming languages. 

Its main purpose is to ensure that tokens can only be generated and decrypted by official applications within my project. Additionally, Zetoken is used to obfuscate identities, such as user IDs or other object IDs, when the data is transmitted through public spaces. 

Currently, Zetoken supports integration with Python, Node.js, and PHP.

However, to comply with the global cybersecurity standard "Don't roll your own crypto", I hereby declare that it is:

**NOT SUITABLE** for:
- Storing highly sensitive data (banking infrastructure, credit cards, medical records)
- Password hashing (primary passwords)
- National-scale critical financial systems

**HIGHLY SUITABLE** for:
- Quiz or online exam answer tokens
- Ticket tokens or temporary access vouchers
- Obfuscation (securely masking IDs or URL parameters)
- Other non-financial application needs requiring mass token generation

---

## 🚀 Key Features

- **Encryption**  
  Converts text data into unique numeric tokens

- **Decryption**  
  Accurately restores numeric tokens back into the original text data

- **Security**  
  Utilizes:
  - `keyId` (identifier / offset)
  - `secretKey` (primary key)
  
  Tokens can only be read by parties possessing the same keys.

- **Time-Bound Tokens (TTL)**  
  Native support for auto-expiring tokens with built-in NTP Clock Skew tolerance (Leeway).

---

## ⚠️ Weaknesses & Limitations

Please note that Zetoken has several technical limitations:

- Zetoken has not been audited by professional security experts. Therefore, to comply with global security standards, Zetoken is not yet suitable for financial, medical, or critical infrastructure scales.

- The infancy of the algorithm potentially introduces zero-day security vulnerabilities. Therefore, for now, Zetoken should only be used for hashing non-risky or low-risk data.

---

## ⚠️ WARNING: ENV CONFIGURATION REQUIRED

This library **WILL NOT WORK** if you do not define the security keys.

Zetoken **does not have fallback keys** for security reasons. You **MUST** include the following configuration in your Environment system / `.env` file of your project:

```env
ZETOKEN_ACCESS_KEY_ID="your_unique_identity"
ZETOKEN_SECRET_KEY="your_secret_key"
ZETOKEN_ITERATIONS=1000

```

If the keys are not found in the ENV or function parameters:

* All **encryption** processes will fail
* All **decryption** processes will fail
* The function will immediately return: `false`

---

## 🛠️ Generator Tool

Use the following tool to generate our official cryptographic configuration components:

👉 **[OPEN ZETOKEN GENERATOR](https://anonputraid.github.io/zetoken.html)**

---

## 🧪 Stress Test Results (100,000 Iterations)

```text
==================================================
STARTING ULTIMATE STRESS TEST: 100,000 ITERATIONS (PURE PHP)
==================================================     

Final Results:
- Total Execution Time : 732.71 seconds
- Average Encryption   : 3.66690 ms
- Average Decryption   : 3.65769 ms
- Worst Latency        : 132.3242 ms
- Total Failures       : 0
- PHP Memory Delta     : 0.39 KB

==================================================

```

---

## ⚙️ System Requirements

Ensure your server or system meets the following modern standards:

* **PHP >= 8.2** (Required for `\Random\Randomizer` extension support and PCG64 engine).
* PHP extension `openssl` must be enabled.

---

## 📦 Installation

Use Composer:

```bash
composer require zetwypro/zetoken

```

---

## 💻 Usage Instructions

### 1. Standard Usage (Automatically from ENV)

This method is the simplest as it automatically retrieves keys from the `.env`.

```php
use Zetwypro\Zetoken\Zetoken;

$zetoken = new Zetoken();

// Encode using KeyID, Secret, & Iterations from .env
$token =$zetoken->encode("Secret Message");

// Decode and perfectly restore to original text
$original = $zetoken->decode($token);

```

---

### 2. Sign & VerifySign Features (3-Layer Security / Manual KeyID)

Use this feature if you want to bind a token exclusively to an entity (e.g., User ID, Transaction Number). Even if the keys are compromised, `User A`'s token cannot be used by `User B`.

```php
use Zetwypro\Zetoken\Zetoken;

$zetoken = new Zetoken();

$userId = "USER-9921";
$data = "Exam Passed";

// SIGN: Locks the token using a combination of Master Access Key + $userId + Master Secret Key
$token =$zetoken->sign($data,$userId);

// VERIFY: The token can only be opened and its integrity verified if the User ID is an exact match
$result =$zetoken->verifySign($token,$userId);

if ($result === false) {
    echo "Fake token, manipulated, or incorrect KeyID!";
}

```

---

### 3. Time-Bound Tokens (TTL & Leeway)

You can generate tokens that automatically expire after a certain amount of time (Time-To-Live). Zetoken internally validates the expiration and provides a default `leeway` of 60 seconds to accommodate minor server clock desynchronization (NTP Clock Skew).

```php
use Zetwypro\Zetoken\Zetoken;

$zetoken = new Zetoken();

// 1. ENCODE WITH EXPIRATION
// Add the $ttl parameter (in seconds). E.g., 300 seconds = 5 minutes.
// Parameters: encode($text,$keyId, $secretKey,$ttl)
$token = $zetoken->encode("Self-destructing message", ttl: 300);

// You can also use TTL with the Sign feature:
// Parameters: sign($text,$keyId, $secretKey,$ttl)
// $token =$zetoken->sign("Exam Passed", "USER-9921", null, 300);


// 2. DECODE WITH AUTOMATIC TIME VALIDATION
// When decoding, Zetoken automatically checks the time. 
// It includes a default leeway of 60 seconds.
$original = $zetoken->decode($token);

if ($original === false) {
    echo "Token is either invalid, manipulated, or has expired!";
}
    
// Optional: You can customize the leeway time (in seconds)
// Parameters: decode($cipherText,$keyId, $secretKey,$leeway)
// $original = $zetoken->decode($token, null, null, 30);

```

---

## 📄 License

MIT License

Created by **Anonputraid**
