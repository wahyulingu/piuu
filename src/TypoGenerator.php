<?php

namespace WahyuLingu\Piuu;

class TypoGenerator
{
    /**
     * Menghasilkan huruf salah (typo) berdasarkan tetangga keyboard QWERTY.
     * Kasus khusus: jika huruf adalah "o", dengan probabilitas 30% mengembalikan "e".
     *
     * @param  string  $correctLetter  Huruf yang seharusnya.
     * @return string Huruf typo yang dihasilkan.
     */
    public function getWrongLetter(string $correctLetter): string
    {
        $neighbors = [
            'q' => ['w', 'a', 's'],
            'w' => ['q', 'e', 'a', 's', 'd'],
            'e' => ['w', 'r', 's', 'd', 'f'],
            'r' => ['e', 't', 'd', 'f', 'g'],
            't' => ['r', 'y', 'f', 'g', 'h'],
            'y' => ['t', 'u', 'g', 'h', 'j'],
            'u' => ['y', 'i', 'h', 'j', 'k'],
            'i' => ['u', 'o', 'j', 'k', 'l'],
            'o' => ['i', 'p', 'k', 'l'],
            'p' => ['o', 'l'],
            'a' => ['q', 'w', 's', 'z'],
            's' => ['q', 'w', 'e', 'a', 'd', 'z', 'x'],
            'd' => ['w', 'e', 'r', 's', 'f', 'x', 'c'],
            'f' => ['e', 'r', 't', 'd', 'g', 'c', 'v'],
            'g' => ['r', 't', 'y', 'f', 'h', 'v', 'b'],
            'h' => ['t', 'y', 'u', 'g', 'j', 'b', 'n'],
            'j' => ['y', 'u', 'i', 'h', 'k', 'n', 'm'],
            'k' => ['u', 'i', 'o', 'j', 'l', 'm'],
            'l' => ['i', 'o', 'p', 'k'],
            'z' => ['a', 's', 'x'],
            'x' => ['z', 's', 'd', 'c'],
            'c' => ['x', 'd', 'f', 'v'],
            'v' => ['c', 'f', 'g', 'b'],
            'b' => ['v', 'g', 'h', 'n'],
            'n' => ['b', 'h', 'j', 'm'],
            'm' => ['n', 'j', 'k'],
        ];

        $lower = strtolower($correctLetter);
        if ($lower === 'o' && mt_rand(0, 99) < 30) {
            return ctype_upper($correctLetter) ? 'E' : 'e';
        }
        if (isset($neighbors[$lower]) && ! empty($neighbors[$lower])) {
            $randomNeighbor = $neighbors[$lower][array_rand($neighbors[$lower])];

            return ctype_upper($correctLetter) ? strtoupper($randomNeighbor) : $randomNeighbor;
        }
        $alphabet = 'abcdefghijklmnopqrstuvwxyz';
        $alphabet = str_replace($lower, '', $alphabet);
        $random = $alphabet[rand(0, strlen($alphabet) - 1)];

        return ctype_upper($correctLetter) ? strtoupper($random) : $random;
    }

    /**
     * Menghasilkan huruf vokal yang tertukar secara acak.
     *
     * @param  string  $vowel  Huruf vokal yang seharusnya.
     * @return string Huruf vokal yang tertukar.
     */
    public function getSwappedVowel(string $vowel): string
    {
        $vowelsLower = ['a', 'e', 'i', 'o', 'u'];
        $vowelsUpper = ['A', 'E', 'I', 'O', 'U'];
        if (ctype_upper($vowel)) {
            $otherVowels = array_filter($vowelsUpper, function ($v) use ($vowel) {
                return $v !== $vowel;
            });
            $otherVowels = array_values($otherVowels);

            return $otherVowels[array_rand($otherVowels)];
        }
        $otherVowels = array_filter($vowelsLower, function ($v) use ($vowel) {
            return $v !== $vowel;
        });
        $otherVowels = array_values($otherVowels);

        return $otherVowels[array_rand($otherVowels)];
    }
}
