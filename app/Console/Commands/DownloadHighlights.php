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
            ->whereNull('downloaded')
            ->whereNotNull('game_id')
            // updated_at asc to give time for uploading to streamable
            ->orderBy('updated_at', 'ASC')
            ->get();
        $tweets->load(['game.league', 'game.homeTeam', 'game.awayTeam', 'team']);

        foreach($tweets as $tweet){
            echo $tweet->video_url.'...';
            // create curl resource
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tweet->video_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            $output = curl_exec($ch);
            curl_close($ch);

            // Save to path on Digital Ocean
            $filePath = $tweet->getVideoPath();
            $response = Storage::disk('ocean')->put($filePath, $output, 'public');

            // Mark as downloaded
            $tweet->downloaded = $response;
            $tweet->save();

            echo "done.\n";
        }
    }
}