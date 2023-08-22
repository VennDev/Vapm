<?php

namespace vennv\vapm\simultaneous\enums;

enum StatusPromise: string
{
    case PENDING = "pending";
    case FULFILLED = "fulfilled";
    case REJECTED = "rejected";
}