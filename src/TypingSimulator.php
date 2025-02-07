<?php

namespace WahyuLingu\Piuu;

class TypingSimulator
{
    protected ActionExecutor $executor;

    protected DelayHelper $delayHelper;

    protected TypoGenerator $typoGenerator;

    public function __construct(ActionExecutor $executor, DelayHelper $delayHelper, TypoGenerator $typoGenerator)
    {
        $this->executor = $executor;
        $this->delayHelper = $delayHelper;
        $this->typoGenerator = $typoGenerator;
    }

    /**
     * Mensimulasikan pengetikan teks secara humanized.
     *
     * @param  string  $text  Teks yang akan diketik.
     * @param  callable  $sendKeyAction  Callback untuk mengirim satu key.
     * @param  callable|null  $callback  Optional callback untuk logging.
     */
    public function sendKeysHumanized(string $text, callable $sendKeyAction, ?callable $callback = null): void
    {
        $pendingCorrection = null;
        $chars = str_split($text);

        foreach ($chars as $char) {
            if ($pendingCorrection !== null) {
                $pendingCorrection['counter']++;
                if ($char === ' ' || $pendingCorrection['counter'] >= $pendingCorrection['threshold']) {
                    if (mt_rand(0, 999) !== 0) {
                        for ($i = 0; $i < $pendingCorrection['counter']; $i++) {
                            $this->executor->execute(function () use ($sendKeyAction) {
                                $sendKeyAction('LEFT');
                            }, 'sendKey', 'LEFT', $callback);
                            $this->delayHelper->delay(50000, 150000, $callback);
                        }
                        $this->executor->execute(function () use ($sendKeyAction) {
                            $sendKeyAction('BACKSPACE');
                        }, 'sendKey', 'BACKSPACE', $callback);
                        $this->delayHelper->delay(50000, 150000, $callback);
                        $this->executor->execute(function () use ($sendKeyAction, $pendingCorrection) {
                            $sendKeyAction($pendingCorrection['correctLetter']);
                        }, 'sendKey', $pendingCorrection['correctLetter'], $callback);
                        $this->delayHelper->delay(50000, 150000, $callback);
                        for ($i = 0; $i < $pendingCorrection['counter']; $i++) {
                            $this->executor->execute(function () use ($sendKeyAction) {
                                $sendKeyAction('RIGHT');
                            }, 'sendKey', 'RIGHT', $callback);
                            $this->delayHelper->delay(50000, 150000, $callback);
                        }
                    }
                    $pendingCorrection = null;
                }
            }

            if ($pendingCorrection === null && mt_rand(0, 99) < 15) {
                $errorType = rand(0, 3);  // 0: immediate, 1: delayed, 2: skip, 3: vowel swap.
                $leaveUncorrected = (mt_rand(0, 999) === 0);
                switch ($errorType) {
                    case 0:
                        $wrongLetter = $this->typoGenerator->getWrongLetter($char);
                        $this->executor->execute(function () use ($sendKeyAction, $wrongLetter) {
                            $sendKeyAction($wrongLetter);
                        }, 'sendKey', $wrongLetter, $callback);
                        $this->delayHelper->delay(50000, 150000, $callback);
                        if (! $leaveUncorrected) {
                            $this->executor->execute(function () use ($sendKeyAction) {
                                $sendKeyAction('BACKSPACE');
                            }, 'sendKey', 'BACKSPACE', $callback);
                            $this->delayHelper->delay(50000, 150000, $callback);
                            $this->executor->execute(function () use ($sendKeyAction, $char) {
                                $sendKeyAction($char);
                            }, 'sendKey', $char, $callback);
                        }
                        if (mt_rand(0, 99) < 20) {
                            $this->delayHelper->delay(150000, 300000, $callback);
                        }

                        continue 2;
                    case 1:
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
                        $this->delayHelper->delay(50000, 150000, $callback);

                        continue 2;
                    case 3:
                        if (strpos('aeiouAEIOU', $char) !== false) {
                            $swapped = $this->typoGenerator->getSwappedVowel($char);
                            $this->executor->execute(function () use ($sendKeyAction, $swapped) {
                                $sendKeyAction($swapped);
                            }, 'sendKey', $swapped, $callback);
                            if (! $leaveUncorrected && mt_rand(0, 99) < 50) {
                                $this->delayHelper->delay(50000, 150000, $callback);
                                $this->executor->execute(function () use ($sendKeyAction) {
                                    $sendKeyAction('BACKSPACE');
                                }, 'sendKey', 'BACKSPACE', $callback);
                                $this->delayHelper->delay(50000, 150000, $callback);
                                $this->executor->execute(function () use ($sendKeyAction, $char) {
                                    $sendKeyAction($char);
                                }, 'sendKey', $char, $callback);
                            }

                            continue 2;
                        }
                        $wrongLetter = $this->typoGenerator->getWrongLetter($char);
                        $this->executor->execute(function () use ($sendKeyAction, $wrongLetter) {
                            $sendKeyAction($wrongLetter);
                        }, 'sendKey', $wrongLetter, $callback);
                        $this->delayHelper->delay(50000, 150000, $callback);
                        if (! $leaveUncorrected) {
                            $this->executor->execute(function () use ($sendKeyAction) {
                                $sendKeyAction('BACKSPACE');
                            }, 'sendKey', 'BACKSPACE', $callback);
                            $this->delayHelper->delay(50000, 150000, $callback);
                            $this->executor->execute(function () use ($sendKeyAction, $char) {
                                $sendKeyAction($char);
                            }, 'sendKey', $char, $callback);
                        }

                        continue 2;
                }
            }
            $this->executor->execute(function () use ($sendKeyAction, $char) {
                $sendKeyAction($char);
            }, 'sendKey', $char, $callback);
            if (mt_rand(0, 99) < 10) {
                $this->delayHelper->delay(150000, 300000, $callback);
            } else {
                $this->delayHelper->delay(50000, 150000, $callback);
            }
        }
        if ($pendingCorrection !== null) {
            for ($i = 0; $i < $pendingCorrection['counter']; $i++) {
                $this->executor->execute(function () use ($sendKeyAction) {
                    $sendKeyAction('LEFT');
                }, 'sendKey', 'LEFT', $callback);
                $this->delayHelper->delay(50000, 150000, $callback);
            }
            if (! $pendingCorrection['leaveUncorrected']) {
                $this->executor->execute(function () use ($sendKeyAction) {
                    $sendKeyAction('BACKSPACE');
                }, 'sendKey', 'BACKSPACE', $callback);
                $this->delayHelper->delay(50000, 150000, $callback);
                $this->executor->execute(function () use ($sendKeyAction, $pendingCorrection) {
                    $sendKeyAction($pendingCorrection['correctLetter']);
                }, 'sendKey', $pendingCorrection['correctLetter'], $callback);
                $this->delayHelper->delay(50000, 150000, $callback);
            }
            for ($i = 0; $i < $pendingCorrection['counter']; $i++) {
                $this->executor->execute(function () use ($sendKeyAction) {
                    $sendKeyAction('RIGHT');
                }, 'sendKey', 'RIGHT', $callback);
                $this->delayHelper->delay(50000, 150000, $callback);
            }
        }
    }
}
