<?php

namespace App\Services;

use Cache;
use App\Leagues;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;


class HighlightHelper
{

    public function __construct()
    {
    }

    public static function isHighlight($tweet, Leagues $league)
    {
        $isAVideo = (isset($tweet->extended_entities->media[0]->media_url) &&  $tweet->extended_entities->media[0]->type == 'video');

        if(!$isAVideo){
            return false;
        }

        print "Checking vision...\n";

        // Google vision stuff
        $imageUrl = $tweet->extended_entities->media[0]->media_url;

        // Remember the results of checked tweets for 1 hour
        $output = Cache::remember('image-check-'.$tweet->id, 60, function () use ($imageUrl, $league) {

                # instantiates a client
                $imageAnnotator = new ImageAnnotatorClient();

                # prepare the image to be annotated
                $image = file_get_contents($imageUrl);

                # performs label detection on the image file
                $response = $imageAnnotator->labelDetection($image);
                $labels = $response->getLabelAnnotations();

                if(!$labels){
                    return false;
                }

                foreach($labels as $label){
                    if(self::isLabelValid($label->getDescription(), round($label->getScore() * 100))){
                        return true;
                    }
                }

                return false;
        });

        return $output;
    }

    private static function isLabelValid($description, $score)
    {
        // Must have sports venue in the label with a confidence of 95 or greater
        if($description != 'Sports Venue' && $score < 95){
            return false;
        }

        return true;
    }
}