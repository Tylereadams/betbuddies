<?php

namespace App\Services;

class StreamableService
{
    public function __construct($url)
    {
        $this->baseUrl = 'https://api.streamable.com';
        $this->url = $url;
    }

    public function uploadVideo()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl."/import?url=".$this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($ch, CURLOPT_USERPWD, config('services.streamable.email') . ":" . config('services.streamable.password'));

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            dd('Error:' . curl_error($ch));
        }

        curl_close ($ch);

        return $result;
    }

}