<?php

namespace WahyuLingu\Piuu;

class ClickSimulator
{
    protected ActionExecutor $executor;

    protected DelayHelper $delayHelper;

    public function __construct(ActionExecutor $executor, DelayHelper $delayHelper)
    {
        $this->executor = $executor;
        $this->delayHelper = $delayHelper;
    }

    /**
     * Mensimulasikan aksi klik dengan delay sebelum dan sesudahnya.
     *
     * @param  callable  $clickAction  Callback untuk melakukan aksi klik.
     * @param  callable|null  $callback  Optional callback untuk logging.
     */
    public function clickHumanized(callable $clickAction, ?callable $callback = null): void
    {
        $this->delayHelper->delay(100000, 300000, $callback);
        $this->executor->execute(function () use ($clickAction) {
            $clickAction();
        }, 'click', null, $callback);
        $this->delayHelper->delay(100000, 300000, $callback);
    }
}
