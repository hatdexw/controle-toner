<?php
namespace App\Core;
/**
 * Simple in-memory (per-request) + optional file cache with TTL.
 * File cache is optional; by default only static array for reuse within same request.
 */
class Cache {
    private static array $memory = [];
    public static function remember(string $key, int $ttlSeconds, callable $resolver) {
        $now = time();
        if (isset(self::$memory[$key]) && self::$memory[$key]['expires'] > $now) {
            return self::$memory[$key]['value'];
        }
        $value = $resolver();
        self::$memory[$key] = ['value'=>$value,'expires'=>$now + $ttlSeconds];
        return $value;
    }
}
