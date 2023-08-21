<?php

namespace vennv\api\simultaneous;

interface SampleMacroInterface
{
    public function isRepeat(): bool;

    public function getTimeOut(): float;

    public function getTimeStart(): float;

    public function getCallback(): callable;

    public function getId(): int;

    public function checkTimeOut(): bool;

    public function resetTimeOut(): void;

    public function isRunning(): bool;

    public function run(): void;

    public function stop(): void;
}
