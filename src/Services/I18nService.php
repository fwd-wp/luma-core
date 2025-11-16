<?php 
namespace Luma\Core\Services;

class I18nService {
    protected static string $domain = '';

    public static function setDomain(string $domain): void {
        self::$domain = $domain;
    }

    public static function getDomain(): string {
        return self::$domain;
    }
}