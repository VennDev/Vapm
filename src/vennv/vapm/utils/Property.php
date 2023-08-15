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

declare(strict_types = 1);

namespace vennv\vapm\utils;

use vennv\vapm\express\JsonData;
use vennv\vapm\express\StaticData;
use Exception;
use function property_exists;

// This is trait for JsonData|StaticData class
trait Property {

    /**
     * @param mixed $data
     * @param array<string, mixed> $options
     * @param bool $forced
     * @return JsonData|StaticData
     * @throws Exception
     */
    public function update(mixed $data, array $options, bool $forced = true) : JsonData|StaticData {
        if ($data instanceof StaticData || $data instanceof JsonData) {
            foreach ($options as $key => $value) {
                try {
                    if (property_exists($data, $key)) {
                        $data->$key = $value;
                    }
                } catch (Exception $e) {
                    if (!$forced) {
                        throw $e;
                    }

                    continue;
                }
            }

            return $data;
        } else {
            throw new Exception('Invalid data type provided.');
        }
    }

}
