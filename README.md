# PIUU

**PIUU (PHP Imitation User Utility)** adalah package PHP yang menyediakan utilitas untuk mensimulasikan interaksi pengguna secara natural. Package ini memungkinkan Anda untuk mensimulasikan pengetikan (dengan delay acak, typo, koreksi tertunda, skip letter, dan vowel swap) serta aksi klik dengan delay yang menyerupai perilaku manusia.

## Fitur Utama

- **Humanized Typing Simulation**  
  Mensimulasikan pengetikan dengan:

  - **Delay Acak**: Jeda antar karakter yang menyerupai kecepatan mengetik manusia.
  - **Typo Simulation**: Menyisipkan kesalahan pengetikan (typo) dengan immediate atau delayed correction.
  - **Skip Letter**: Mensimulasikan kelalaian dalam mengetik suatu huruf.
  - **Vowel Swap**: Mengganti huruf vokal (misalnya, 'o' dapat tertukar menjadi 'e') untuk mensimulasikan kesalahan umum.

- **Humanized Click Simulation**  
  Mensimulasikan aksi klik dengan delay sebelum dan sesudah klik agar terasa natural.

- **Modular & Universal**  
  Package ini dirancang secara modular dan tidak bergantung langsung pada objek WebDriver. Semua aksi dijalankan melalui callback sehingga mudah diintegrasikan ke berbagai proyek.  
  Komponen utama meliputi:
  - **ActionExecutor**: Menjalankan aksi (closure) dengan dukungan callback untuk logging atau aksi tambahan.
  - **DelayHelper**: Menyediakan delay acak (dalam mikrodetik) untuk mensimulasikan jeda manusia.
  - **TypoGenerator**: Menghasilkan typo berdasarkan tetangga keyboard QWERTY dan menyediakan fungsi vowel swap.
  - **TypingSimulator**: Mensimulasikan pengetikan dengan berbagai kemungkinan kesalahan (typo) dan koreksi tertunda.
  - **ClickSimulator**: Mensimulasikan aksi klik dengan delay natural.
  - **HumanizedActions**: Facade untuk mengakses fungsi typing dan klik secara humanized melalui callback.

## Instalasi

Pastikan Anda sudah menginstal PHP 8.2 atau lebih tinggi dan Composer. Untuk menginstal package ini, jalankan perintah berikut pada terminal di direktori project Anda:

```bash
composer require wahyulingu/piuu
```

## Struktur Direktori

Setelah instalasi, struktur direktori package Anda akan terlihat seperti berikut:

```
piuu/
├── composer.json
├── README.md
└── src/
    ├── ActionExecutor.php
    ├── DelayHelper.php
    ├── TypoGenerator.php
    ├── TypingSimulator.php
    ├── ClickSimulator.php
    └── HumanizedActions.php
```

## Cara Penggunaan

### 1. Setup dan Inisialisasi

```php
<?php
require 'vendor/autoload.php';

use WahyuLingu\Piuu\ActionExecutor;
use WahyuLingu\Piuu\DelayHelper;
use WahyuLingu\Piuu\TypoGenerator;
use WahyuLingu\Piuu\TypingSimulator;
use WahyuLingu\Piuu\ClickSimulator;
use WahyuLingu\Piuu\HumanizedActions;

$executor = new ActionExecutor();
$delayHelper = new DelayHelper($executor);
$typoGenerator = new TypoGenerator();
$typingSimulator = new TypingSimulator($executor, $delayHelper, $typoGenerator);
$clickSimulator = new ClickSimulator($executor, $delayHelper);

$humanizedActions = new HumanizedActions($typingSimulator, $clickSimulator);
```

### 2. Mensimulasikan Pengetikan

```php
<?php
$sendKeyAction = function(string $key) {
    echo "Send key: {$key}\n";
};

$callback = function(string $phase, string $actionName, $data) {
    echo "[{$phase}] {$actionName}: " . json_encode($data) . "\n";
};

$text = "Hello, world! This is PIUU.";
$humanizedActions->sendKeysHumanized($text, $sendKeyAction, $callback);
```

### 3. Mensimulasikan Klik

```php
<?php
$clickAction = function() {
    echo "Click executed\n";
};

$humanizedActions->clickHumanized($clickAction, $callback);
```

### 4. Menyisipkan Delay Mandiri

```php
<?php
$humanizedActions->delay(100000, 300000, $callback);
```

## Kontribusi

Kontribusi sangat kami sambut! Jika Anda memiliki ide perbaikan atau ingin menambahkan fitur baru, silakan:

1. Fork repository ini.
2. Buat branch fitur atau perbaikan bug.
3. Ajukan pull request dengan deskripsi perubahan.
4. Buka issue jika ada pertanyaan atau masalah.

## Lisensi

Package ini dilisensikan di bawah [MIT License](LICENSE).

## Persyaratan

- PHP >= 8.2
- Composer

## Dukungan

Untuk pertanyaan, saran, atau bantuan, silakan buka issue di [GitHub repository](https://github.com/wahyulingu/piuu) atau hubungi penulis melalui email: **wahyulingu@gmail.com**.
