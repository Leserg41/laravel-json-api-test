<?php

namespace App\Cache;

use Illuminate\Support\Facades\Storage;
use App\Http\Resources\TVShowResource;
use App\Models\TVShow;

class FileCache
{
    const CACHE_LIFETIME = 1000;

    public static function save (string $name, array $data)
    {
        $fileName = 'request-' . md5($name) .'.json';

        Storage::put($fileName, json_encode([
            'data' => $data,
            'createdAt' => time()
        ]));
    }

    public static function get (string $name)
    {
        $fileName = 'request-' . md5($name) .'.json';

        $file = Storage::get($fileName);
        $data = json_decode($file, true);

        if ($data && self::isExpired($data['createdAt'])) 
        {
            return null;
        }

        return $data;
    }

    public static function isExpired (int $createdAtTimestamp): bool
    {
        return !(($createdAtTimestamp + self::CACHE_LIFETIME) > time());
    }
}
