<?php

namespace vennv;

interface InterfaceAsync
{

    /**
     * @param Promise|Async|callable $callable $callable $callable
     * @return mixed
     *
     * This function is used to create a new async await function
     * You should use this function in an async function
     */
    public static function await(Promise|Async|callable $callable) : mixed;

    /**
     * @return int
     *
     * This function is used to get the id of the async function
     */
    public function getId() : int;

}