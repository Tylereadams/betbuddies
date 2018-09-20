<?php

namespace App\Providers;

use Carbon\Carbon;
use GuzzleHttp;
use Illuminate\Support\ServiceProvider;
use Cache;

class FullStoryProvider extends ServiceProvider
{

    public function __construct()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'https://www.fullstory.com/api/v1/',
            'headers' => [
                'Authorization: Basic' => env('FULLSTORY_API_KEY'),
            ],
        ]);
    }

    public function getUserSessions($email)
    {
        $res = $this->client->request('GET', 'sessions', ['email' => $email]);

        return $res;
    }

}