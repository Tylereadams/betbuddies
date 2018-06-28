<?php

namespace App\Http\Controllers;

use App\Leagues;
use App\Teams;
use Illuminate\Support\Facades\Request;
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
        $teams = Teams::where('slug', Request::get('team'))->get();

        $data['teams'] = [];

        foreach($teams as $team){

            $teamData = [
                'twitter' => $team->twitter,
                'league' => $team->league->name
            ];

            $timeline = Cache::remember($team->id.'-'.$team->league->name.'-'.$team->twitter, 10, function ()use($team) {
                return Twitter::getUserTimeline(['screen_name' => $team->twitter, 'count' => 100]);
            });

            foreach($timeline as $tweet) {

                if(isset($tweet->extended_entities->media[0]->media_url) && $tweet->extended_entities->media[0]->type == 'video'){
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
