<?php

namespace App\Services;

use Cache;
use App\Leagues;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;


class HighlightHelper
{
    const WHITELIST = [
        'sport venue',
    ];

    const BLACKLIST = [
        'structure'
    ];

    public function __construct()
    {
    }

    public static function isHighlight($tweet, Leagues $league)
    {
        $isAVideo = (isset($tweet->extended_entities->media[0]->media_url) &&  $tweet->extended_entities->media[0]->type == 'video');

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

                    if(($score < 95) || !in_array($description, Self::WHITELIST) || in_array($description, Self::BLACKLIST)){
                        print "failed: ".$description." ".$score."\n\n";
                        continue;
                    }

                    print "passed: ".$description." ".$score."\n\n";

                    return true;
                }

                return false;
        });

        return $output;
    }

}