<?php

namespace App\Http\Controllers;

use App\UsersBets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Games;
use Carbon\Carbon;

class BetsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
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
    public function store()
    {
        $gameId = Input::get('gameId');

        $game = Games::findOrFail($gameId);

        if(!$game->isBettable()){
            return response()->json(['errors' => ['Game has already started']]);
        }

        // validate
        $rules = array(
            'amount'       => 'required',
            'spread'       => 'required',
            'teamId'       => 'required',
            'gameId'       => 'required'
        );
        $messages = [
            'teamId.required' => 'Pick a team.'
        ];
        $validator = Validator::make(Input::all(), $rules, $messages);

        // process the login
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $userBet = new UsersBets();
        $userBet->amount = Input::get('amount');
        $userBet->spread = Input::get('spread');
        $userBet->team_id = Input::get('teamId');
        $userBet->game_id = $gameId;
        $userBet->user_id = Auth::id();

        $userBet->save();

        return response()->json($userBet->getCardData());
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
     * @param  UsersBets  $usersBet
     * @return Response
     */
    public function delete(UsersBets $usersBets)
    {
        // Make sure it can be deleted
        if($usersBets->user_id == Auth::id()){
            $usersBets->delete();
        }

        if(!$usersBets->game->isBettable()){
            return response()->json(['errors' => 'Game has already started.']);
        }

        return response()->json([], 200);
    }

    /**
     * Accepts an existing bet
     * @param UsersBets $userBet
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function accept(UsersBets $usersBets)
    {
        if(Auth::id() == $usersBets->user_id){
            return back()->withErrors(['errors' => 'Can\'t accept your own bet. Accept someone else\'s, dummy.']);
        }

        if(!$usersBets->game->isBettable()){
            return back()->withErrors(['errors' => 'Game has already started.']);
        }

        $usersBets->opponent_id = Auth::id();
        $usersBets->opponent_team_id = $usersBets->team_id == $usersBets->game->homeTeam->id ? $usersBets->game->awayTeam->id : $usersBets->game->homeTeam->id;
        $usersBets->save();

        return response()->json($usersBets);
    }
}
