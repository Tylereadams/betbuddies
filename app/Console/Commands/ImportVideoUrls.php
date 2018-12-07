<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StreamableService;
use App\TweetLogs;

class ImportVideoUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betbuddies:import-video-urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets video urls for highlights with streamable code, but no video url';

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
        // Get tweets missing the video url, but have a streamable code
        $tweets = TweetLogs::whereNotNull('streamable_code')
            ->whereNull('video_url')
            ->get();

        foreach($tweets as $tweet){
            $streamable = new StreamableService();
            $streamableUrl = $streamable->getVideoUrl($tweet->streamable_code);

            if(!$streamableUrl){
                continue;
            }

            // Save video_url
            $tweet->video_url = 'https:'.$streamableUrl;
            $tweet->save();
        }
    }
}
