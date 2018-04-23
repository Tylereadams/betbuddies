<?php

namespace App\Http\Controllers;

use App\Teams;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Thujohn\Twitter\Facades\Twitter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MachineLearningController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $teams = Teams::all();

        $data['teams'] = [];
        foreach($teams as $team){

            if(getenv('TWITTER_CONSUMER_KEY'.$team->getKey()) === false){
//                echo "Skipping ".$team->nickname."\n";
                continue;
            }

            // Get the config for this team's twitter account
            Twitter::reconfig([
                'consumer_key' => env('TWITTER_CONSUMER_KEY'.$team->getKey()),
                'consumer_secret' => env('TWITTER_CONSUMER_SECRET'.$team->getKey()),
                'token' => env('TWITTER_ACCESS_TOKEN'.$team->getKey()),
                'secret' => env('TWITTER_ACCESS_TOKEN_SECRET'.$team->getKey())
            ]);


            $teamData = [
                'twitter' => $team->twitter,
                'league' => $team->league->name
            ];

            $dataPath = base_path().'/storage/machine_learning/data';

            $folders = ['good', 'bad'];
            foreach($folders as $folder){
                $files = scandir($dataPath.'/'.$team->league->name.'/'.$folder);
                foreach($files as $file){
                    $existingTweets[] = $file;
                }
            }

            $timeline = Cache::remember($team->id.'-'.$team->league->name.'-'.$team->twitter, 10, function ()use($team) {
                return Twitter::getUserTimeline(['screen_name' => $team->twitter, 'count' => 100]);
            });

            foreach($timeline as $tweet) {
                if(in_array($tweet->id, $existingTweets)){
                    continue;
                }

                if(
                    isset($tweet->extended_entities->media[0]->media_url)
                    && $tweet->extended_entities->media[0]->type == 'video'
                ){

                    $teamData['tweets'][] = [
                            'id' => $tweet->id
                    ];
                }
            }

            // Forget team data if there aren't any tweets
            if(!isset($teamData['tweets'])){
                continue;
            }

            $data['teams'][] = $teamData;
        }

        return view('machine-admin', $data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store($tweetId)
    {
        $league = \Request::get('league');
        $imageType = \Request::has('goodImage') ? 'good' : 'bad';
        $tweet = Twitter::getTweet($tweetId, ['include_entities' => 1, 'trim_user' => 1]);

        $imageUrl = $tweet->extended_entities->media[0]->media_url;

        $img = '../storage/machine_learning/data/'.$league.'/'.$imageType.'/'.$tweetId.'.jpg';
        $file = file_get_contents($imageUrl);
        $output = file_put_contents($img, $file);

        if(!$output){
            Session::flash('message', "Oops, we couldn't add that image");
        } else {
            Session::flash('message', 'Image added');
        }

        return redirect('machine-learning');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

}
