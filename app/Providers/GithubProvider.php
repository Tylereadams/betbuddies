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

    /**
     * Find issues for this repo on github
     * @return mixed
     */
    public function getIssues()
    {
        $res = $this->client->request('GET', 'repos/tylereadams/betbuddies/issues');

        return json_decode($res->getBody());
    }

    /**
     * Find user data from github
     * @param $username
     * @return mixed
     */
    public function getUser($username)
    {
        $res = $this->client->request('GET', 'users/'.$username);

        return json_decode($res->getBody());
    }

    /**
     * Alternative way to find the email, github wasn't returning the email even though it's public on some accounts.
     * This will find the email based on commits to public repo's.
     * @param $username
     * @return bool
     */
    public function searchForEmail($username)
    {
        $res = $this->client->request('GET', 'users/'.$username.'/events/public');

        $events = json_decode($res->getBody());

        foreach($events as $event){
            if($event->type == 'PushEvent'){
                return $event->payload->commits[0]->author->email;
            }
        }

        return false;
    }

}