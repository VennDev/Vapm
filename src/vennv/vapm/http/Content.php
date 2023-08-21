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

enum Content: string
{
    case CONTENT_TYPE = 'Content-Type';

    case CONTENT_LENGTH = 'Content-Length';

    case CONTENT_ENCODING = 'Content-Encoding';

    case CONTENT_DISPOSITION = 'Content-Disposition';

    case CONTENT_TRANSFER_ENCODING = 'Content-Transfer-Encoding';

    case CONTENT_RANGE = 'Content-Range';

    case CONTENT_LOCATION = 'Content-Location';

    case CONTENT_LANGUAGE = 'Content-Language';

    case CONTENT_EXPIRES = 'Content-Expires';

    case CONTENT_MD5 = 'Content-MD5';

    case CONTENT_RANGE_BYTES = 'bytes';

    case CONTENT_RANGE_NONE = '*';
}
