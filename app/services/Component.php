<?php


namespace MyApp\Services;


class Component
{

    public function __construct($di)
    {
        $this->di = $di;
    }


    public function __get($name)
    {
        return $this->di[$name];
    }

}