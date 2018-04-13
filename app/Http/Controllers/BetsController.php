<?php

namespace App\Http\Controllers;

use App\UsersBets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

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
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $rules = array(
            'amount'       => 'required',
            'spread'       => 'required',
            'teamId'       => 'required',
            'gameId'       => 'required',
        );
        $messages = [
            'teamId.required' => 'Pick a team.'
        ];
        $validator = Validator::make(Input::all(), $rules, $messages);

        // process the login
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $userBet = new UsersBets();
        $userBet->amount = Input::get('amount');
        $userBet->spread = Input::get('spread');
        $userBet->team_id = Input::get('teamId');
        $userBet->game_id = Input::get('gameId');
        $userBet->user_id = Auth::id();

        $userBet->save();

        Session::flash('message', 'Bet created!');

        return redirect()->route('game', ['urlSegment' => $userBet->game->url_segment]);
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

    // TODO: NOT SURE WHAT TO DO HERE
    public function accept(UsersBets $userBet)
    {
        $userBet->opponent_id = Auth::id();
        $userBet->team_id = ($userBet->game->homeTeam->id == $userBet->team_id ? $userBet->game->awayTeam->id : $userBet->game->homeTeam->id);
        $userBet->save();

        return back();
    }
}
