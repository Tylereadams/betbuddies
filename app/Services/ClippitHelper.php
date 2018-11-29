<?php
/**
 * Created by PhpStorm.
 * User: tyler
 * Date: 11/28/18
 * Time: 7:42 PM
 */

namespace App\Services;

use Goutte\Client;

class ClippitHelper
{
    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getVideoSource()
    {
        // Crawl the page and find the mp4 file.
        $client = new Client();

        $crawler = $client->request('GET', $this->url);
        $videoSource = $crawler->filter('#player-container')->extract(array('data-hd-file'));

        // Returns an array, first one is what we want
        return reset($videoSource);
    }

}