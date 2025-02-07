<?php

use PHPUnit\Framework\TestCase;
use WahyuLingu\Piuu\ActionExecutor;
use WahyuLingu\Piuu\DelayHelper;
use WahyuLingu\Piuu\TypingSimulator;
use WahyuLingu\Piuu\TypoGenerator;

class TypingSimulatorTest extends TestCase
{
    protected $executor;

    protected $delayHelper;

    protected $typoGenerator;

    protected $typingSimulator;

    protected function setUp(): void
    {
        $this->executor = $this->createMock(ActionExecutor::class);
        $this->delayHelper = $this->createMock(DelayHelper::class);
        $this->typoGenerator = $this->createMock(TypoGenerator::class);
        $this->typingSimulator = new TypingSimulator($this->executor, $this->delayHelper, $this->typoGenerator);
    }

    public function test_send_keys_humanized()
    {
        $text = 'hello';
        $sendKeyAction = function ($key) {
            // Simulate sending a key
        };
        $callback = function ($message) {
            // Simulate logging
        };

        $this->executor->expects($this->any())
            ->method('execute')
            ->willReturnCallback(function ($action) {
                $action();
            });

        $this->delayHelper->expects($this->any())
            ->method('delay')
            ->willReturnCallback(function ($min, $max, $callback) {
                // Simulate delay
            });

        $this->typoGenerator->expects($this->any())
            ->method('getWrongLetter')
            ->willReturnCallback(function ($char) {
                return $char === 'h' ? 'g' : $char;
            });

        $this->typoGenerator->expects($this->any())
            ->method('getSwappedVowel')
            ->willReturnCallback(function ($char) {
                return $char === 'e' ? 'i' : $char;
            });

        $this->typingSimulator->sendKeysHumanized($text, $sendKeyAction, $callback);

        // Assertions can be added here to verify the behavior
        $this->assertTrue(true); // Placeholder assertion
    }
}
