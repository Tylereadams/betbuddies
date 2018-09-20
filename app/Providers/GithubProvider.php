<?php

namespace App\Providers;

use GuzzleHttp;
use Illuminate\Support\ServiceProvider;
use Cache;

class GithubProvider extends ServiceProvider
{

    public function __construct()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization: token' => env('GITHUB_API_KEY'),
                'Accept' => 'application/vnd.github.v3+json'
            ],
        ]);
    }

    public function getIssues()
    {
        $res = $this->client->request('GET', 'repos/tylereadams/betbuddies/issues');

        return json_decode($res->getBody());
    }

    public function getUser($username)
    {
        $res = $this->client->request('get', 'users/'.$username);

        return json_decode($res->getBody());
    }

}