<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TweetLogs;
use Illuminate\Support\Facades\Storage;

class DownloadHighlights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betbuddies:download-highlights';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads highlights from tweets with video urls that haven\'t been downloaded';

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
        // Find tweets that have not been downloaded
        $tweets = TweetLogs::whereNotNull('video_url')
            ->orderBy('created_at', 'DESC')
            ->get();
        $tweets->load(['game.league', 'game.homeTeam', 'game.awayTeam', 'team']);

        foreach($tweets as $tweet){
            if(!$tweet->video_url){
                continue;
            }

            // Save to path on Digital Ocean
            $filePath = $tweet->getVideoPath();
            $response = Storage::disk('ocean')->put($filePath, file_get_contents($tweet->video_url), 'public');

            // Mark as downloaded
            $tweet->downloaded = $response;
            $tweet->save();
        }
    }
}