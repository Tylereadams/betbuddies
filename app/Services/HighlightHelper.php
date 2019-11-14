<?php

namespace App\Services;

use Cache;
use App\Leagues;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;


class HighlightHelper
{
    const WHITELIST = [
        'sport venue',
        'hockey',
        'ice hockey',
        'basketball moves',
        'player',
    ];

    const BLACKLIST = [
        'structure',
        'crowd',
        'audience',
        'fan',
        'madden'
    ];

    const THRESHOLD = 90;

    public function __construct()
    {
    }

    public static function isHighlight($tweet, Leagues $league)
    {
        $isAVideo = (isset($tweet->extended_entities->media[0]->media_url) &&  str_contains($tweet->extended_entities->media[0]->expanded_url, 'video'));

        if(!$isAVideo){
            return false;
        }

        // Google vision stuff
        $imageUrl = $tweet->extended_entities->media[0]->media_url;

        // Remember the results of checked tweets for 1 day
        $output = Cache::remember('image-check-'.$tweet->id, 60 * 24, function () use ($imageUrl, $league) {

                print "Checking vision...\n";

                print $imageUrl." \n";

                // Instantiates Google Vision
                $imageAnnotator = new ImageAnnotatorClient();
                $image = file_get_contents($imageUrl);
                $response = $imageAnnotator->labelDetection($image);
                $labels = $response->getLabelAnnotations();

                if(!$labels){
                    return false;
                }

                foreach($labels as $label){

                    $description = strtolower($label->getDescription());
                    $score = (int) round($label->getScore() * 100, 2);

                    if(($score > Self::THRESHOLD) && in_array($description, Self::WHITELIST) && !in_array($description, Self::BLACKLIST)){
                        print "passed: ".$description." ".$score."\n";

                        return true;
                    }

                    print "failed: ".$description." ".$score."\n\n";
                    continue;
                }

                return false;
        });

        return $output;
    }

}