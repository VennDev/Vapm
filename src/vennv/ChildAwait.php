<?php

namespace vennv;

final class ChildAwait {

    public function __construct(
        private \Fiber | null $current,
        private \Fiber $fiber,
    ) {}

    public function getCurrent() : \Fiber | null {
        return $this->current;
    }

    public function getFiber() : \Fiber {
        return $this->fiber;
    }

}