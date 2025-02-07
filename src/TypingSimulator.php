<?php

namespace WahyuLingu\Piuu;

/**
 * Kelas TypingSimulator
 *
 * Kelas ini mensimulasikan pengetikan secara humanis dengan dua pendekatan:
 *
 * 1. **sendKeysHumanized()**
 *    Metode ini mensimulasikan pengetikan yang menyerupai perilaku manusia, yaitu:
 *    - Mengetik teks dengan kecepatan yang tidak konsisten (delay acak).
 *    - Menyisipkan kesalahan pengetikan (typo) secara acak dengan probabilitas tertentu.
 *    - Mengoreksi kesalahan secara natural, seperti memindahkan kursor ke kiri, menghapus kesalahan,
 *      mengetik ulang karakter yang benar, dan mengembalikan posisi kursor.
 *
 * 2. **sendKeysHumanizedWithoutErrors()**
 *    Metode ini mensimulasikan pengetikan dengan delay acak tanpa menyisipkan kesalahan pengetikan.
 *
 * Untuk menjalankan simulasi ini, kelas menggunakan tiga objek:
 * - **ActionExecutor**: Untuk mengeksekusi aksi pengiriman key (misalnya, mengetik karakter atau perintah khusus).
 * - **DelayHelper**: Untuk mengatur delay atau jeda antar aksi agar meniru kecepatan pengetikan manusia.
 * - **TypoGenerator**: Untuk menghasilkan kesalahan ketik (typo) dan manipulasi karakter (misalnya, mendapatkan
 *    huruf salah atau menukar huruf vokal).
 *
 * Selain itu, beberapa key khusus didefinisikan langsung sebagai konstanta dengan nilai Unicode, antara lain:
 * - **ARROW_LEFT**: Untuk memindahkan kursor ke kiri.
 * - **BACKSPACE**: Untuk menghapus karakter.
 * - **ARROW_RIGHT**: Untuk memindahkan kursor ke kanan.
 */
class TypingSimulator
{
    // Definisi key-key yang dibutuhkan secara langsung.
    // Nilai-nilai berikut menggunakan representasi Unicode yang sama seperti pada implementasi asli.
    private const ARROW_LEFT = "\xEE\x80\x92";

    private const BACKSPACE = "\xEE\x80\x83";

    private const ARROW_RIGHT = "\xEE\x80\x94";

    protected ActionExecutor $executor;

    protected DelayHelper $delayHelper;

    protected TypoGenerator $typoGenerator;

    /**
     * Konstruktor untuk kelas TypingSimulator.
     *
     * @param  ActionExecutor  $executor  Objek untuk mengeksekusi aksi (misal: pengiriman key).
     * @param  DelayHelper  $delayHelper  Objek pembantu untuk mengatur jeda antar aksi.
     * @param  TypoGenerator  $typoGenerator  Objek untuk menghasilkan kesalahan ketik (typo) dan manipulasi karakter.
     */
    public function __construct(ActionExecutor $executor, DelayHelper $delayHelper, TypoGenerator $typoGenerator)
    {
        $this->executor = $executor;
        $this->delayHelper = $delayHelper;
        $this->typoGenerator = $typoGenerator;
    }

    /**
     * Mensimulasikan pengetikan teks secara humanis dengan kemungkinan kesalahan ketik.
     *
     * Metode ini mengemulasi bagaimana manusia mengetik dengan:
     * - Kecepatan yang tidak konsisten (delay acak).
     * - Kesalahan pengetikan (typo) yang muncul secara acak.
     * - Mekanisme koreksi kesalahan yang natural.
     *
     * **Algoritma dan Langkah-langkah:**
     *
     * 1. **Inisialisasi dan Persiapan:**
     *    - Teks yang akan diketik dipecah menjadi array karakter.
     *    - Variabel `$pendingCorrection` diinisialisasi dengan `null`. Variabel ini akan menyimpan
     *      informasi tentang kesalahan pengetikan yang belum dikoreksi, meliputi:
     *         - `correctLetter`: Karakter yang seharusnya diketik.
     *         - `counter`: Jumlah karakter salah yang telah diketik.
     *         - `threshold`: Batas minimal jumlah kesalahan yang harus terjadi sebelum dilakukan koreksi.
     *         - `leaveUncorrected`: Flag yang menentukan apakah kesalahan dibiarkan tanpa koreksi.
     *
     * 2. **Iterasi Setiap Karakter:**
     *    - Lakukan iterasi pada setiap karakter dalam teks.
     *
     * 3. **Pengecekan dan Penanganan Koreksi Tertunda:**
     *    - Jika variabel `$pendingCorrection` tidak `null` (artinya ada kesalahan yang belum dikoreksi):
     *         a. Tambahkan nilai `counter` pada `$pendingCorrection`.
     *         b. Jika karakter saat ini adalah spasi **atau** nilai `counter` telah mencapai `threshold`:
     *            - Lakukan proses koreksi dengan langkah-langkah berikut:
     *              i. **Memindahkan Kursor ke Kiri:**
     *                 Kirimkan perintah `ARROW_LEFT` sebanyak jumlah kesalahan (nilai `counter`).
     *              ii. **Menghapus Karakter Salah:**
     *                  Kirimkan perintah `BACKSPACE` untuk menghapus karakter yang salah.
     *              iii. **Mengetik Karakter yang Benar:**
     *                   Ketik karakter yang benar, yang disimpan dalam `$pendingCorrection['correctLetter']`.
     *              iv. **Memindahkan Kursor ke Kanan:**
     *                  Kirimkan perintah `ARROW_RIGHT` sebanyak jumlah kesalahan untuk mengembalikan posisi kursor.
     *            - Setelah koreksi, set `$pendingCorrection` ke `null`.
     *
     * 4. **Sisipkan Kesalahan (Typo):**
     *    - Jika tidak ada koreksi tertunda, periksa dengan probabilitas 2% apakah akan menyisipkan kesalahan.
     *    - Jika diputuskan untuk menyisipkan kesalahan, pilih secara acak salah satu dari empat jenis kesalahan:
     *
     *         a. **Immediate Error (Kasus 0):**
     *            - **Langkah-langkah:**
     *              1. Dapatkan huruf salah menggunakan `$typoGenerator->getWrongLetter($char)`.
     *              2. Ketik huruf salah tersebut.
     *              3. Jika flag `leaveUncorrected` tidak aktif (dengan probabilitas 99% untuk dikoreksi),
     *                 segera koreksi kesalahan dengan:
     *                     - Mengirim perintah `BACKSPACE` untuk menghapus huruf salah.
     *                     - Mengetik kembali huruf yang benar.
     *
     *         b. **Delayed Error (Kasus 1):**
     *            - **Langkah-langkah:**
     *              1. Ketik huruf salah (sama seperti kasus Immediate Error).
     *              2. Simpan informasi koreksi dalam variabel `$pendingCorrection` yang berisi:
     *                     - `correctLetter`: Huruf yang seharusnya.
     *                     - `counter`: Diinisialisasi 0.
     *                     - `threshold`: Angka acak antara 1 sampai 3.
     *                     - `leaveUncorrected`: Flag untuk menentukan apakah kesalahan dikoreksi.
     *              3. Koreksi akan dilakukan pada iterasi berikutnya ketika ditemukan kondisi pemicu (misal, spasi).
     *
     *         c. **Skip Error (Kasus 2):**
     *            - **Langkah-langkah:**
     *              1. Tidak melakukan aksi mengetik kesalahan.
     *              2. Hanya menambahkan delay acak untuk mensimulasikan jeda yang terjadi.
     *
     *         d. **Vowel Swap Error (Kasus 3):**
     *            - **Langkah-langkah:**
     *              1. Jika karakter yang sedang diketik merupakan vokal (contoh: a, e, i, o, u),
     *                 tukar huruf tersebut dengan vokal lain menggunakan `$typoGenerator->getSwappedVowel($char)`.
     *              2. Ketik huruf yang telah ditukar.
     *              3. Dengan probabilitas hampir pasti (99%), lakukan koreksi:
     *                     - Mengirim perintah `BACKSPACE` untuk menghapus huruf salah.
     *                     - Mengetik kembali huruf yang benar.
     *
     *    - Pada masing-masing jenis kesalahan, delay acak disisipkan agar pengetikan tampak lebih natural.
     *
     * 5. **Pengiriman Karakter Normal:**
     *    - Jika tidak ada kesalahan yang disisipkan, karakter dikirimkan secara normal melalui callback.
     *    - Delay acak juga disisipkan untuk meniru kecepatan pengetikan manusia.
     *
     * 6. **Penyelesaian Koreksi Tertunda:**
     *    - Setelah seluruh karakter teks telah diiterasi, periksa kembali variabel `$pendingCorrection`.
     *    - Jika masih ada koreksi tertunda, lakukan langkah-langkah koreksi (memindahkan kursor ke kiri,
     *      BACKSPACE, mengetik karakter yang benar, dan memindahkan kursor ke kanan) seperti yang dijelaskan di atas.
     *
     * Seluruh proses ini bertujuan untuk menghasilkan simulasi pengetikan yang menyerupai cara manusia
     * mengetik dengan ketidaksempurnaan (typo) yang kemudian dikoreksi secara natural.
     *
     * @param  string  $text  Teks yang akan diketik.
     * @param  callable  $sendKeyAction  Callback untuk mengirimkan satu karakter atau key.
     * @param  callable|null  $callback  Callback opsional untuk logging atau aksi tambahan.
     */
    public function sendKeysHumanized(string $text, callable $sendKeyAction, ?callable $callback = null): void
    {
        $pendingCorrection = null;
        $chars = str_split($text);

        foreach ($chars as $char) {
            // Jika terdapat koreksi yang tertunda, tambahkan counter dan periksa apakah sudah saatnya koreksi.
            if ($pendingCorrection !== null) {
                $pendingCorrection['counter']++;
                // Jika karakter saat ini adalah spasi atau jumlah kesalahan sudah mencapai threshold, lakukan koreksi.
                if ($char === ' ' || $pendingCorrection['counter'] >= $pendingCorrection['threshold']) {
                    // Dengan probabilitas tertentu, lakukan proses koreksi.
                    if (mt_rand(0, 999) !== 0) {
                        // Langkah 3a: Pindahkan kursor ke kiri sebanyak jumlah kesalahan.
                        for ($i = 0; $i < $pendingCorrection['counter']; $i++) {
                            $this->executor->execute(function () use ($sendKeyAction) {
                                $sendKeyAction(self::ARROW_LEFT);
                            }, 'sendKey', self::ARROW_LEFT, $callback);
                            $this->delayHelper->delay(50000, 150000, $callback);
                        }
                        // Langkah 3b: Hapus karakter yang salah.
                        $this->executor->execute(function () use ($sendKeyAction) {
                            $sendKeyAction(self::BACKSPACE);
                        }, 'sendKey', self::BACKSPACE, $callback);
                        $this->delayHelper->delay(50000, 150000, $callback);
                        // Langkah 3c: Ketik karakter yang benar.
                        $this->executor->execute(function () use ($sendKeyAction, $pendingCorrection) {
                            $sendKeyAction($pendingCorrection['correctLetter']);
                        }, 'sendKey', $pendingCorrection['correctLetter'], $callback);
                        $this->delayHelper->delay(50000, 150000, $callback);
                        // Langkah 3d: Pindahkan kursor ke kanan untuk mengembalikan posisi.
                        for ($i = 0; $i < $pendingCorrection['counter']; $i++) {
                            $this->executor->execute(function () use ($sendKeyAction) {
                                $sendKeyAction(self::ARROW_RIGHT);
                            }, 'sendKey', self::ARROW_RIGHT, $callback);
                            $this->delayHelper->delay(50000, 150000, $callback);
                        }
                    }
                    // Reset variabel koreksi setelah proses koreksi selesai.
                    $pendingCorrection = null;
                }
            }

            // Jika tidak ada koreksi tertunda, tentukan apakah akan menyisipkan kesalahan.
            // Probabilitas penyisipan kesalahan adalah 2%.
            if ($pendingCorrection === null && mt_rand(0, 99) < 2) {
                // Pilih secara acak jenis kesalahan antara 0 dan 3:
                // 0: Immediate Error, 1: Delayed Error, 2: Skip Error, 3: Vowel Swap Error.
                $errorType = rand(0, 3);
                // Tentukan probabilitas untuk membiarkan kesalahan tanpa koreksi (1% kasus dibiarkan).
                $leaveUncorrected = (mt_rand(0, 99) === 0);
                switch ($errorType) {
                    case 0:
                        // **Immediate Error:**
                        // a. Dapatkan huruf salah melalui TypoGenerator.
                        // b. Ketik huruf salah.
                        // c. Jika tidak dibiarkan, segera koreksi dengan BACKSPACE dan mengetik huruf yang benar.
                        $wrongLetter = $this->typoGenerator->getWrongLetter($char);
                        $this->executor->execute(function () use ($sendKeyAction, $wrongLetter) {
                            $sendKeyAction($wrongLetter);
                        }, 'sendKey', $wrongLetter, $callback);
                        $this->delayHelper->delay(50000, 150000, $callback);
                        if (! $leaveUncorrected) {
                            $this->executor->execute(function () use ($sendKeyAction) {
                                $sendKeyAction(self::BACKSPACE);
                            }, 'sendKey', self::BACKSPACE, $callback);
                            $this->delayHelper->delay(50000, 150000, $callback);
                            $this->executor->execute(function () use ($sendKeyAction, $char) {
                                $sendKeyAction($char);
                            }, 'sendKey', $char, $callback);
                        }
                        // Kadang-kadang tambahkan delay ekstra.
                        if (mt_rand(0, 99) < 20) {
                            $this->delayHelper->delay(150000, 300000, $callback);
                        }

                        continue 2;
                    case 1:
                        // **Delayed Error:**
                        // a. Ketik huruf salah.
                        // b. Simpan informasi koreksi ke dalam $pendingCorrection:
                        //    - Huruf yang benar, counter diinisialisasi 0, threshold acak antara 1-3,
                        //      dan flag leaveUncorrected.
                        $wrongLetter = $this->typoGenerator->getWrongLetter($char);
                        $this->executor->execute(function () use ($sendKeyAction, $wrongLetter) {
                            $sendKeyAction($wrongLetter);
                        }, 'sendKey', $wrongLetter, $callback);
                        $pendingCorrection = [
                            'correctLetter' => $char,
                            'counter' => 0,
                            'threshold' => rand(1, 3),
                            'leaveUncorrected' => $leaveUncorrected,
                        ];

                        continue 2;
                    case 2:
                        // **Skip Error:**
                        // Hanya menambahkan delay untuk mensimulasikan jeda tanpa mengirimkan kesalahan.
                        $this->delayHelper->delay(50000, 150000, $callback);

                        continue 2;
                    case 3:
                        // **Vowel Swap Error:**
                        // a. Jika karakter adalah vokal, tukar dengan huruf vokal lain.
                        // b. Ketik huruf yang telah ditukar.
                        // c. Dengan probabilitas 99%, lakukan koreksi: hapus huruf salah dan ketik huruf yang benar.
                        if (strpos('aeiouAEIOU', $char) !== false) {
                            $swapped = $this->typoGenerator->getSwappedVowel($char);
                            $this->executor->execute(function () use ($sendKeyAction, $swapped) {
                                $sendKeyAction($swapped);
                            }, 'sendKey', $swapped, $callback);
                            if (! $leaveUncorrected && mt_rand(0, 99) < 99) {
                                $this->delayHelper->delay(50000, 150000, $callback);
                                $this->executor->execute(function () use ($sendKeyAction) {
                                    $sendKeyAction(self::BACKSPACE);
                                }, 'sendKey', self::BACKSPACE, $callback);
                                $this->delayHelper->delay(50000, 150000, $callback);
                                $this->executor->execute(function () use ($sendKeyAction, $char) {
                                    $sendKeyAction($char);
                                }, 'sendKey', $char, $callback);
                            }

                            continue 2;
                        }
                        // Jika bukan vokal, lakukan seperti Immediate Error.
                        $wrongLetter = $this->typoGenerator->getWrongLetter($char);
                        $this->executor->execute(function () use ($sendKeyAction, $wrongLetter) {
                            $sendKeyAction($wrongLetter);
                        }, 'sendKey', $wrongLetter, $callback);
                        $this->delayHelper->delay(50000, 150000, $callback);
                        if (! $leaveUncorrected) {
                            $this->executor->execute(function () use ($sendKeyAction) {
                                $sendKeyAction(self::BACKSPACE);
                            }, 'sendKey', self::BACKSPACE, $callback);
                            $this->delayHelper->delay(50000, 150000, $callback);
                            $this->executor->execute(function () use ($sendKeyAction, $char) {
                                $sendKeyAction($char);
                            }, 'sendKey', $char, $callback);
                        }

                        continue 2;
                }
            }
            // Kirimkan karakter secara normal jika tidak ada kesalahan yang disisipkan.
            $this->executor->execute(function () use ($sendKeyAction, $char) {
                $sendKeyAction($char);
            }, 'sendKey', $char, $callback);
            // Sisipkan delay acak untuk meniru kecepatan pengetikan manusia.
            if (mt_rand(0, 99) < 10) {
                $this->delayHelper->delay(150000, 300000, $callback);
            } else {
                $this->delayHelper->delay(50000, 150000, $callback);
            }
        }
        // Setelah iterasi, periksa apakah masih ada koreksi yang tertunda.
        if ($pendingCorrection !== null) {
            // Lakukan koreksi final: pindahkan kursor ke kiri, hapus, ketik huruf yang benar, dan kembalikan posisi.
            for ($i = 0; $i < $pendingCorrection['counter']; $i++) {
                $this->executor->execute(function () use ($sendKeyAction) {
                    $sendKeyAction(self::ARROW_LEFT);
                }, 'sendKey', self::ARROW_LEFT, $callback);
                $this->delayHelper->delay(50000, 150000, $callback);
            }
            if (! $pendingCorrection['leaveUncorrected']) {
                $this->executor->execute(function () use ($sendKeyAction) {
                    $sendKeyAction(self::BACKSPACE);
                }, 'sendKey', self::BACKSPACE, $callback);
                $this->delayHelper->delay(50000, 150000, $callback);
                $this->executor->execute(function () use ($sendKeyAction, $pendingCorrection) {
                    $sendKeyAction($pendingCorrection['correctLetter']);
                }, 'sendKey', $pendingCorrection['correctLetter'], $callback);
                $this->delayHelper->delay(50000, 150000, $callback);
            }
            for ($i = 0; $i < $pendingCorrection['counter']; $i++) {
                $this->executor->execute(function () use ($sendKeyAction) {
                    $sendKeyAction(self::ARROW_RIGHT);
                }, 'sendKey', self::ARROW_RIGHT, $callback);
                $this->delayHelper->delay(50000, 150000, $callback);
            }
        }
    }

    /**
     * Mensimulasikan pengetikan teks secara humanis tanpa kesalahan ketik.
     *
     * Metode ini mengirimkan setiap karakter secara langsung dengan delay acak,
     * sehingga meniru kecepatan pengetikan manusia namun tanpa menyisipkan kesalahan (typo).
     *
     * @param  string  $text  Teks yang akan diketik.
     * @param  callable  $sendKeyAction  Callback untuk mengirimkan satu karakter atau key.
     * @param  callable|null  $callback  Callback opsional untuk logging atau aksi tambahan.
     */
    public function sendKeysHumanizedWithoutErrors(string $text, callable $sendKeyAction, ?callable $callback = null): void
    {
        $chars = str_split($text);
        foreach ($chars as $char) {
            // Kirimkan karakter secara langsung tanpa kesalahan.
            $this->executor->execute(function () use ($sendKeyAction, $char) {
                $sendKeyAction($char);
            }, 'sendKey', $char, $callback);

            // Sisipkan delay acak untuk meniru kecepatan pengetikan manusia.
            if (mt_rand(0, 99) < 10) {
                $this->delayHelper->delay(150000, 300000, $callback);
            } else {
                $this->delayHelper->delay(50000, 150000, $callback);
            }
        }
    }
}
