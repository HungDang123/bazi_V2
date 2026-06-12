<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * Cache kết quả BaZi trong cùng request — tránh tính 2 lần khi build Q1 + Q2.
 */
class PdfBaziCache
{
    /** @var array<string, array<string, mixed>> */
    private static array $store = [];

    /**
     * @param  callable(): array<string, mixed>  $builder
     * @return array<string, mixed>
     */
    public static function remember(Request $req, callable $builder): array
    {
        $key = self::key($req);
        if (isset(self::$store[$key])) {
            return self::$store[$key];
        }

        self::$store[$key] = $builder();

        return self::$store[$key];
    }

    private static function key(Request $req): string
    {
        return hash('xxh128', serialize([
            (string) $req->input('full_name', ''),
            (int) $req->input('y', 0),
            (int) $req->input('m', 0),
            (int) $req->input('d', 0),
            $req->filled('h') ? (int) $req->input('h') : null,
            $req->filled('minute') ? (int) $req->input('minute') : null,
            (string) $req->input('g', 'male'),
        ]));
    }
}
