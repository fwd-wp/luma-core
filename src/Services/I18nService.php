<?php

namespace Luma\Core\Services;

class I18nService
{
    private static string $domain = 'luma-core';

    public static function get_domain(): string
    {
        return self::$domain;
    }

    public static function set_domain(string $domain)
    {
        self::$domain = $domain;
    }
}
