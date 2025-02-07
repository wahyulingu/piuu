<?php

namespace WahyuLingu\Piuu;

class HumanizedActions
{
    public function __construct(
        protected readonly DelayHelper $delayHelper,
        protected readonly TypingSimulator $typingSimulator,
        protected readonly ClickSimulator $clickSimulator) {}

    /**
     * Facade untuk mengirimkan teks dengan pengetikan humanized.
     *
     * @param  string  $text  Teks yang akan diketik.
     * @param  callable  $sendKeyAction  Callback untuk mengirim key.
     * @param  callable|null  $callback  Optional callback untuk logging.
     */
    public function sendKeysHumanized(string $text, callable $sendKeyAction, ?callable $callback = null): void
    {
        $this->typingSimulator->sendKeysHumanized($text, $sendKeyAction, $callback);
    }

    /**
     * Facade untuk mengirimkan teks dengan pengetikan humanized tanpa error.
     *
     * @param  string  $text  Teks yang akan diketik.
     * @param  callable  $sendKeyAction  Callback untuk mengirim key.
     * @param  callable|null  $callback  Optional callback untuk logging.
     */
    public function sendKeysHumanizedWithoutErrors(string $text, callable $sendKeyAction, ?callable $callback = null): void
    {
        $this->typingSimulator->sendKeysHumanizedWithoutErrors($text, $sendKeyAction, $callback);
    }

    /**
     * Facade untuk melakukan klik dengan delay humanized.
     *
     * @param  callable  $clickAction  Callback untuk aksi klik.
     * @param  callable|null  $callback  Optional callback.
     */
    public function clickHumanized(callable $clickAction, ?callable $callback = null): void
    {
        $this->clickSimulator->clickHumanized($clickAction, $callback);
    }

    /**
     * Facade untuk menyisipkan delay humanized secara mandiri.
     *
     * @param  int  $minDelay  Minimum delay (mikrodetik).
     * @param  int  $maxDelay  Maksimum delay (mikrodetik).
     * @param  callable|null  $callback  Optional callback.
     */
    public function delay(int $minDelay, int $maxDelay, ?callable $callback = null): void
    {
        $this->delayHelper->delay($minDelay, $maxDelay, $callback);
    }
}
