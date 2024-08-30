<?php

namespace Obrainwave\Paygate\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Obrainwave\Paygate\Skeleton
 */
class Paygate extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Obrainwave\Paygate\PaygateManager::class;
    }
}
