<?php

namespace vennv\api\simultaneous;

use Throwable;
use vennv\vapm\simultaneous\Promise;

interface StreamInterface
{

    /**
     * @throws Throwable
     *
     * Use this to read a file or url.
     */
    public static function read(string $path): Promise;

    /**
     * @throws Throwable
     *
     * Use this to write to a file.
     */
    public static function write(string $path, string $data): Promise;

    /**
     * @throws Throwable
     *
     * Use this to append to a file.
     */
    public static function append(string $path, string $data): Promise;

    /**
     * @throws Throwable
     *
     * Use this to delete a file.
     */
    public static function delete(string $path): Promise;

    /**
     * @throws Throwable
     *
     * Use this to create a file.
     */
    public static function create(string $path): Promise;

    /**
     * @throws Throwable
     *
     * Use this to create a file or overwrite a file.
     */
    public static function overWrite(string $path, string $data): Promise;
}
