<?php

namespace App\Console\Commands;

use App\TweetLogs;
use Illuminate\Console\Command;
use App\Services\StreamableService;

class ImportStreamableCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betbuddies:import-streamable-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uploads tweet to streamable and records the code';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // All tweets that don't have a video url or streamable_code
        $tweets = TweetLogs::whereNull('streamable_code')
            ->whereNull('video_url')
            ->orderBy('created_at', 'DESC')
            ->get();

        foreach($tweets as $tweet){
            echo $tweet->getTweetUrl().'...';

            $streamable = new StreamableService($tweet);
            $response = json_decode($streamable->uploadVideo());

            if(!isset($response->shortcode)){
                echo "---------NOT FOUND-------.\n";
                continue;
            }
            $streamableCode = $response->shortcode;

            $tweet->streamable_code = $streamableCode;
            $tweet->save();
            echo "done.\n";
        }
    }
}
