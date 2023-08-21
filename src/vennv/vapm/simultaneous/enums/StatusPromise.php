<?php

namespace vennv\vapm\enums;

enum StatusPromise: string
{
    case PENDING = "pending";
    case FULFILLED = "fulfilled";
    case REJECTED = "rejected";
}