<?php

namespace Markings\Actions;

use Illuminate\Support\Facades\Http;

class Api
{
    public function __construct()
    {

    }

    public static function getEnvironments()
    {
        $token = config('markings.api_token');

        return Http::withHeaders([
            'Authorization' => "Bearer $token",
        ])
            ->acceptJson()
            ->withOptions(['verify' => false])
            ->get(rtrim(config('markings.api_url'), '/').'/environments');
    }

    public static function syncEvents($events)
    {
        $token = config('markings.api_token');

        return Http::withHeaders([
            'Authorization' => "Bearer $token",
        ])
            ->acceptJson()
            ->withOptions(['verify' => false])
            ->post(rtrim(config('markings.api_url'), '/').'/events/sync', [
                'environment' => config('markings.environment'),
                'events' => $events
            ]);
    }

    public static function syncTypes($types)
    {
        $token = config('markings.api_token');

        return Http::withHeaders([
            'Authorization' => "Bearer $token",
        ])
            ->acceptJson()
            ->withOptions(['verify' => false])
            ->post(rtrim(config('markings.api_url'), '/').'/types', [
                'environment' => config('markings.environment'),
                'types' => $types
            ]);
    }
}
