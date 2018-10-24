<?php

namespace App\Services;

use Cache;
use App\Leagues;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

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

        // Google vision stuff
        $imageUrl = $tweet->extended_entities->media[0]->media_url;

        // Remember the results of checked tweets for 12 hours
        $output = Cache::remember('image-check-'.$tweet->id, 60 * 12, function () use ($imageUrl, $league) {
            $process = new Process("python storage/machine_learning/image_script.py ".$imageUrl." ".$league->name);
            $process->run();

            // executes after the command finishes
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);

                return false;
            }
            $output = str_replace(array("\n", ""), '', $process->getOutput());

            // output is a string: "[0]" or "[1]"
            return (bool) $output[1];
        });

        return $output;
    }
}