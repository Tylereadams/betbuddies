<?php

namespace App\Http\Controllers;

use App\Players;
use App\Services\TwitterHelper;
use App\TeamCredentials;
use App\TweetLogs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use App\Teams;
use Thujohn\Twitter\Facades\Twitter;

class AdminController extends Controller
{


    /**
     * Redirect the user to the Twitter authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('twitter')->redirect();
    }

    /**
     * Obtain the user information from Twitter.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $twitterUser = Socialite::driver('twitter')->user();

        // Save token here
        $credentials = TeamCredentials::where('username', $twitterUser->nickname)->firstOrFail();

        $credentials->token = $twitterUser->token;
        $credentials->token_secret = encrypt($twitterUser->tokenSecret);
        $credentials->email = $twitterUser->email ?: $credentials->email;
        $credentials->location = $twitterUser->user['location'];
        $credentials->name = $twitterUser->name;
        $credentials->username = $twitterUser->nickname;

        $credentials->save();

        return redirect(url('/team/edit?teamId='.$credentials->team_id));
    }

    /**
     * Display team edit page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editTeamTwitter()
    {
        $teams = Teams::orderBy('nickname')->get();
        $teams->load(['league', 'credentials']);

        dd(getenv('TWITTER_CONSUMER_KEY'));
        $selectedTeam = $teams->first();
        if(Request::get('teamId')) {
            $selectedTeam = Teams::where('id', Request::get('teamId'))->first();

            if($selectedTeam->credentials && $selectedTeam->credentials->token_secret) {
                Twitter::reconfig([
                    'token' => $selectedTeam->credentials->token,
                    'secret' => decrypt($selectedTeam->credentials->token_secret)
                ]);
            }
        }

        $data = [
            'teams' => $teams,
            'selectedTeam' => [
                'id' => $selectedTeam->id ?: null,
                'nickname' => $selectedTeam->nickname ?: null,
                'name' => $selectedTeam->credentials ? $selectedTeam->credentials->name : null,
                'username' => $selectedTeam->credentials ? $selectedTeam->credentials->username : null,
                'email' => $selectedTeam->credentials ? $selectedTeam->credentials->email : null,
                'description' => isset($selectedTeam->credentials->description) ? $selectedTeam->credentials->description : null,
                'location' => $selectedTeam->location ? $selectedTeam->location : null,
                'token' => $selectedTeam->credentials ? $selectedTeam->credentials->token : null,
                'tokenSecret' => $selectedTeam->credentials ? $selectedTeam->credentials->token_secret : null,
            ]
        ];


        return view('admin.edit-team', $data);
    }

    /**
     * Save twitter data from team edit page
     * @param Teams $team
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveTwitterData(Teams $team)
    {
        // Save to DB
        TeamCredentials::updateOrCreate([
            'team_id' => $team->id
        ],[
            'username' => Request::post('username') ?: null,
            'name' => Request::post('name') ?: null,
            'location' => Request::post('location') ?: null,
            'email' => Request::post('email') ?: null,
            'description' => Request::post('description') ?: TwitterHelper::getTeamTwitterDescription($team)
        ]);

        if($team->credentials && $team->credentials->token) {
            // Update Twitter
            Twitter::reconfig([
                'token' => $team->credentials->token,
                'secret' => decrypt(Request::post('tokenSecret'))
            ]);

            Twitter::postProfile([
                'name' => Request::post('name'),
                'description' => Request::post('description'),
                'location' => Request::post('location'),
                'email' => Request::post('email'),
                'description' => Request::post('description')
            ]);
        }

        return redirect()->back();
    }

    /**
     * Display logged tweets all on one page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tweetLog()
    {
        $q = Request::get('q');
        $tweetLogQuery = TweetLogs::whereNotNull('downloaded');

        // Search for player's highlight tweets
        if(Request::has('q')){

            $playerId = Players::where(DB::raw("CONCAT(first_name,' ',last_name)"),  $q)->first()->pluck('id');

            if(!$playerId){
                abort(404);
            }

            $tweetLogQuery->whereHas('players', function($query) use ($playerId){
                $query->where('id', $playerId);
            });
        }

        $tweetPaginator = $tweetLogQuery->orderBy('created_at', 'DESC')->paginate(15);
        $tweetPaginator->load(['team', 'players.tweets', 'game.awayTeam',  'game.homeTeam', 'game.league']);

        $tweets = [];
        $topRelatedPlayers = [];
        foreach($tweetPaginator as $key => $tweet){
            $tweets[$key] = [
                'id' => $tweet->tweet_id,
                'isInvalid' => $tweet->is_invalid,
                'team' => [
                  'twitter' => $tweet->team->twitter,
                  'leagueId' => $tweet->team->league_id
                ],
                'imageUrl' => $tweet->media_url,
                'text' => $tweet->text,
                'highlightUrl' => $tweet->highlightUrl(),
                'players' => $tweet->players->map(function($player){
                    return [
                        'name' => $player->first_name.' '.$player->last_name
                    ];
                }),
                'period' => $tweet->period
            ];

            if(isset($tweets[$key]['mentions'][0])){
                $topRelatedPlayers[] = $tweets[$key]['mentions'][0];
            }

            if(isset($tweet->game)){
                $tweets[$key]['game'] = [
                    'opponent' => $tweet->team->id == $tweet->game->awayTeam->id ? $tweet->game->homeTeam->nickname : $tweet->game->awayTeam->nickname,
                    'date' => Carbon::parse($tweet->game->start_date)->format('m/d')
                ];
            }
        }

        $topRelatedPlayers = collect($topRelatedPlayers)->unique()->take(5)->sortByDesc(function($player){
            return $player['tweetCount'];
        });

        return view('admin.tweet-log', [
            'paginator' => $tweetPaginator,
            'tweets' => $tweets,
            'topRelatedPlayers' => $topRelatedPlayers,
            'searchTerm' => $q
        ]);
    }
}