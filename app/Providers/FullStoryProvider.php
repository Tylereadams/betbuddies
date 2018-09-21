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
                'Authorization' => 'Basic '.env('FULLSTORY_API_KEY'),
            ],
        ]);
    }

    /**
     * Gets FullStory sessions given an email
     * @param $email
     * @return mixed
     */
    public function getUserSessions($email)
    {
        // Email is required for FS API
        if(!$email){
            return;
        }

        // Get the FS sessions by email, limit to 3
        $res = $this->client->request('GET', 'sessions?email='.$email.'&limit=3');

        return json_decode($res->getBody());
    }

}