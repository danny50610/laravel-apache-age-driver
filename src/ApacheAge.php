<?php

namespace Danny50610\LaravelApacheAgeDriver;

use Danny50610\LaravelApacheAgeDriver\Services\ApacheAgeService;
use Illuminate\Support\Facades\Facade;

class ApacheAge extends Facade
{
    public static function getFacadeAccessor()
    {
        return ApacheAgeService::class;
    }
}
