<?php

namespace App\Support;

class ExternalApiEndpoints
{
    private const BASE_URL = 'https://sns_erp.pibin.workers.dev/api';

    public static function mnemonicNew(): string
    {
        return self::BASE_URL . '/mnemonic/new';
    }

    public static function alchemyPricesSymbols(string $symbols): string
    {
        return self::BASE_URL . '/alchemy/prices/symbols?symbols=' . $symbols;
    }

    public static function tatumFees(): string
    {
        return self::BASE_URL . '/tatum/fees';
    }
}
