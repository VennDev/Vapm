<?php

/**
 * Vapm - A library support for PHP about Async, Promise, Coroutine, Thread, GreenThread
 *          and other non-blocking methods. The library also includes some Javascript packages
 *          such as Express. The method is based on Fibers & Generator & Processes, requires
 *          you to have php version from >= 8.1
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

declare(strict_types=1);

namespace vennv\vapm\http;

enum Status: int
{
    case CONTINUE = 100;

    case SWITCHING_PROTOCOLS = 101;

    case PROCESSING = 102;

    case EARLY_HINTS = 103;

    case OK = 200;

    case CREATED = 201;

    case ACCEPTED = 202;

    case NON_AUTHORITATIVE_INFORMATION = 203;

    case NO_CONTENT = 204;

    case RESET_CONTENT = 205;

    case PARTIAL_CONTENT = 206;

    case MULTIPLE_CHOICES = 300;

    case MOVED_PERMANENTLY = 301;

    case FOUND = 302;

    case SEE_OTHER = 303;

    case NOT_MODIFIED = 304;

    case USE_PROXY = 305;

    case UNUSED = 306;

    case TEMPORARY_REDIRECT = 307;

    case PERMANENT_REDIRECT = 308;

    case BAD_REQUEST = 400;

    case UNAUTHORIZED = 401;

    case PAYMENT_REQUIRED = 402;

    case FORBIDDEN = 403;

    case NOT_FOUND = 404;

    case METHOD_NOT_ALLOWED = 405;

    case NOT_ACCEPTABLE = 406;

    case PROXY_AUTHENTICATION_REQUIRED = 407;

    case REQUEST_TIMEOUT = 408;

    case CONFLICT = 409;

    case GONE = 410;

    case LENGTH_REQUIRED = 411;

    case PRECONDITION_FAILED = 412;

    case REQUEST_ENTITY_TOO_LARGE = 413;

    case REQUEST_URI_TOO_LONG = 414;

    case UNSUPPORTED_MEDIA_TYPE = 415;

    case REQUESTED_RANGE_NOT_SATISFIABLE = 416;

    case EXPECTATION_FAILED = 417;

    case IM_A_TEAPOT = 418;

    case MISDIRECTED_REQUEST = 421;

    case UNPROCESSABLE_ENTITY = 422;

    case LOCKED = 423;

    case FAILED_DEPENDENCY = 424;

    case TOO_EARLY = 425;

    case UPGRADE_REQUIRED = 426;

    case PRECONDITION_REQUIRED = 428;

    case TOO_MANY_REQUESTS = 429;

    case REQUEST_HEADER_FIELDS_TOO_LARGE = 431;

    case UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    case INTERNAL_SERVER_ERROR = 500;

    case NOT_IMPLEMENTED = 501;

    case BAD_GATEWAY = 502;

    case SERVICE_UNAVAILABLE = 503;

    case GATEWAY_TIMEOUT = 504;

    case HTTP_VERSION_NOT_SUPPORTED = 505;

    case VARIANT_ALSO_NEGOTIATES = 506;

    case INSUFFICIENT_STORAGE = 507;

    case LOOP_DETECTED = 508;

    case NOT_EXTENDED = 510;

    case NETWORK_AUTHENTICATION_REQUIRED = 511;
}