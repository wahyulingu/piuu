<?php

namespace WahyuLingu\Piuu;

class DelayHelper
{
    protected ActionExecutor $executor;

    public function __construct(ActionExecutor $executor)
    {
        $this->executor = $executor;
    }

    /**
     * Menyisipkan delay acak antara $minDelay dan $maxDelay (dalam mikrodetik).
     *
     * @param  int  $minDelay  Minimum delay dalam mikrodetik.
     * @param  int  $maxDelay  Maksimum delay dalam mikrodetik.
     * @param  callable|null  $callback  Optional callback.
     */
    public function delay(int $minDelay = 50000, int $maxDelay = 150000, ?callable $callback = null): void
    {
        $delay = rand($minDelay, $maxDelay);
        $this->executor->execute(function () use ($delay) {
            usleep($delay);
        }, 'delay', ['min' => $minDelay, 'max' => $maxDelay, 'actual' => $delay], $callback);
    }
}
