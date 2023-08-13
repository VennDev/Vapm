<?php

/**
 * Vapm - A library for PHP about Async, Promise, Coroutine, GreenThread,
 *      Thread and other non-blocking methods. The method is based on Fibers &
 *      Generator & Processes, requires you to have php version from >= 8.1
 *
 * Copyright (C) 2023  VennDev
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

declare(strict_types = 1);

namespace vennv\vapm\http;

use ReflectionClass;

final class Status {

    public const CONTINUE = 100;

    public const SWITCHING_PROTOCOLS = 101;

    public const PROCESSING = 102;

    public const EARLY_HINTS = 103;

    public const OK = 200;

    public const CREATED = 201;

    public const ACCEPTED = 202;

    public const NON_AUTHORITATIVE_INFORMATION = 203;

    public const NO_CONTENT = 204;

    public const RESET_CONTENT = 205;

    public const PARTIAL_CONTENT = 206;

    public const MULTIPLE_CHOICES = 300;

    public const MOVED_PERMANENTLY = 301;

    public const FOUND = 302;

    public const SEE_OTHER = 303;

    public const NOT_MODIFIED = 304;

    public const USE_PROXY = 305;

    public const UNUSED = 306;

    public const TEMPORARY_REDIRECT = 307;

    public const PERMANENT_REDIRECT = 308;

    public const BAD_REQUEST = 400;

    public const UNAUTHORIZED = 401;

    public const PAYMENT_REQUIRED = 402;

    public const FORBIDDEN = 403;

    public const NOT_FOUND = 404;

    public const METHOD_NOT_ALLOWED = 405;

    public const NOT_ACCEPTABLE = 406;

    public const PROXY_AUTHENTICATION_REQUIRED = 407;

    public const REQUEST_TIMEOUT = 408;

    public const CONFLICT = 409;

    public const GONE = 410;

    public const LENGTH_REQUIRED = 411;

    public const PRECONDITION_FAILED = 412;

    public const REQUEST_ENTITY_TOO_LARGE = 413;

    public const REQUEST_URI_TOO_LONG = 414;

    public const UNSUPPORTED_MEDIA_TYPE = 415;

    public const REQUESTED_RANGE_NOT_SATISFIABLE = 416;

    public const EXPECTATION_FAILED = 417;

    public const IM_A_TEAPOT = 418;

    public const MISDIRECTED_REQUEST = 421;

    public const UNPROCESSABLE_ENTITY = 422;

    public const LOCKED = 423;

    public const FAILED_DEPENDENCY = 424;

    public const TOO_EARLY = 425;

    public const UPGRADE_REQUIRED = 426;

    public const PRECONDITION_REQUIRED = 428;

    public const TOO_MANY_REQUESTS = 429;

    public const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;

    public const UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    public const INTERNAL_SERVER_ERROR = 500;

    public const NOT_IMPLEMENTED = 501;

    public const BAD_GATEWAY = 502;

    public const SERVICE_UNAVAILABLE = 503;

    public const GATEWAY_TIMEOUT = 504;

    public const HTTP_VERSION_NOT_SUPPORTED = 505;

    public const VARIANT_ALSO_NEGOTIATES = 506;

    public const INSUFFICIENT_STORAGE = 507;

    public const LOOP_DETECTED = 508;

    public const NOT_EXTENDED = 510;

    public const NETWORK_AUTHENTICATION_REQUIRED = 511;

    public static function getStatusName(mixed $value) : int|string|null {
        $class = new ReflectionClass(self::class);
        $constants = $class->getConstants();
        $constantName = array_search($value, $constants);

        return $constantName !== false ? $constantName : null;
    }

}