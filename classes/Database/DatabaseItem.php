<?php


namespace Database;


interface DatabaseItem
{
    public function exists();

    public function get($column, $clear = false);
}