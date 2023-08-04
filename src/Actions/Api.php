<?php

namespace Markings\Actions;

use Illuminate\Support\Facades\Http;
use Src\DataTransferObjects\EnvironmentDTO;

class Api
{
    public function __construct()
    {

    }

    public static function getEnvironments()
    {
        $token = config('markings.api_token');

        $result = Http::withHeaders([
            'Authorization' => "Bearer $token",
        ])
            ->acceptJson()
            ->withOptions(['verify' => false])
            ->get(rtrim(config('markings.api_url'), '/').'/environments');

        if ($result->failed()) {
            throw new \Exception('Could not fetch environments from Markings. Please check your API token.');
        }

        return collect($result->json('environments'))->map(fn ($environment) => 
            new EnvironmentDTO(
                $environment['name'],
                $environment['main'],
                $environment['locked'],
            )
        );
    }

    public static function createEnvironment(string $environmentName, $copyFrom = null) : bool
    {
        $token = config('markings.api_token');

        $result = Http::withHeaders([
            'Authorization' => "Bearer $token",
        ])
            ->acceptJson()
            ->withOptions(['verify' => false])
            ->post(rtrim(config('markings.api_url'), '/').'/environments', [
                'name' => $environmentName,
                'copy_from' => $copyFrom,
            ]);

        if ($result->ok()) {
            return true;
        }

        if ($result->status() == 422) {
            throw new \Exception($result->json('message'));
        }
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
                'events' => $events,
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
                'types' => $types,
            ]);
    }
}
