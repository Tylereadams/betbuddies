<?php

namespace App\Services;

class StreamableService
{
    public function __construct($videoUrl = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_USERPWD, config('services.streamable.email') . ":" . config('services.streamable.password'));

        $this->curl = $ch;
        $this->baseUrl = 'https://api.streamable.com';
        $this->videoUrl = $videoUrl;
    }

    /**
     * Uploads video to streamable service and returns the response
     * @return mixed
     */
    public function uploadVideo()
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->baseUrl."/import?url=".$this->videoUrl);
        $result = curl_exec($this->curl);

        if (curl_errno($this->curl)) {
            echo 'Error:' . curl_error($this->curl);
        }

        curl_close ($this->curl);

        return $result;
    }

    public function getVideoUrl($shortCode)
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->baseUrl."/videos/".$shortCode);
        $result = curl_exec($this->curl);

        if (curl_errno($this->curl)) {
            print'Error:' . curl_error($this->curl);
        }

        curl_close($this->curl);

        $jsonObject = json_decode($result);

        if(!isset($jsonObject->files->mp4) || !isset($jsonObject->files->mp4->url)){
            return false;
        }

        return $jsonObject->files->mp4->url;
    }

}