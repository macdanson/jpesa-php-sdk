<?php

namespace JPesa\SDK\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use JPesa\SDK\JPesaClient;

/**
 * @method static array credit(array $params)
 * @method static array debit(array $params)
 * @method static array transactionInfo(array $params)
 * @method static array kyc(string $mobile)
 */
class JPesa extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return JPesaClient::class;
    }
}
