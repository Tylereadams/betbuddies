<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Auth;

class UsersBets extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    protected $fillable = array('amount', 'game_id', 'team_id', 'user_id', 'spread');

    /**
     * Relations
     */
    public function game()
    {
        return $this->belongsTo(Games::class, 'game_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Teams::class);
    }

    public function opponent()
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }

    public function opponentTeam()
    {
        return $this->belongsTo(Teams::class, 'opponent_team_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winning_user_id');
    }

    public function loser()
    {
        return $this->belongsTo(User::class,  'losing_user_id');
    }

    public function opponentSpread()
    {
        return ($this->opponentTeam->id == $this->game->homeTeam->id ? $this->game->home_spread : $this->game->away_spread);
    }

    public function getCardData()
    {
        $winner = $this->getWinner();

        $betData = [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatarUrl' => $this->user->avatarUrl,
                'urlSegment' => $this->user->url_segment,
                'isWinner' => $winner && $winner->id == $this->user->id ? true : false,
                'isMe' => $this->user->id == Auth::id() ? true : false,
            ],
            'game' => [
                'id' => $this->game->id,
                'homeTeam' => [
                    'name' => $this->game->homeTeam->nickname,
                    'thumbUrl' => $this->game->homeTeam->logoUrl(),
                    'score' => $this->game->home_score
                ],
                'awayTeam' => [
                    'name' => $this->game->awayTeam->nickname,
                    'thumbUrl' => $this->game->awayTeam->logoUrl(),
                    'score' => $this->game->away_score
                ],
                'urlSegment' => $this->game->url_segment
            ],
            'amount' => (int) $this->amount,
            'spread' => (float) $this->spread,
            'isAcceptable' => $this->isAcceptable(),
            'fromMe' => $this->user_id == Auth::id() ? true : false,
            'isWinner' => $winner && $winner->id == Auth::id() ? true : false,
            'isLoser' => $winner && $winner->id != Auth::id() ? true : false,
            'isHome' => $this->team->id == $this->game->homeTeam->id ? true : false,
            'humanDate' => $this->created_at->diffInHours(Carbon::now()) >= 24 ? $this->created_at->format('M d, Y h:ia') : $this->created_at->diffForHumans()
        ];

        $betData['team'] = $this->game->homeTeam->id == $this->team->id ? $betData['game']['homeTeam'] : $betData['game']['awayTeam'];
        $betData['opponentTeam'] = $this->game->homeTeam->id == $this->team->id ? $betData['game']['awayTeam'] : $betData['game']['homeTeam'];

        // Opponent user data
        if($this->opponent) {
            $betData['opponent'] = [
                'id' => $this->opponent->id,
                'name' => $this->opponent->name,
                'avatarUrl' => $this->opponent->avatarUrl,
                'urlSegment' => $this->opponent->url_segment,
                'isMe' => $this->opponent->id == Auth::id() ? true : false,
                'isWinner' => $winner && $winner->id == $this->opponent->id ? true : false
            ];
        }

        return $betData;
    }

    /**
     * Returns team of opponent
     * @return mixed
     */
    public function getOpponentTeam()
    {
        return $this->game->homeTeam->id == $this->team->id ? $this->game->awayTeam : $this->game->homeTeam;
    }

    /**
     * Checks if this bet can be accepted
     * @return bool
     */
    public function isAcceptable()
    {
         if(!$this->opponent_team_id && $this->game->isBettable()){
             return true;
         }

         return false;
    }

    /**
     * Returns a winning user objects only if a valid bet was completed
     * @return bool|mixed
     */
    public function getWinner()
    {
        if(!$this->game->ended_at || !$this->opponent_id){
            return false;
        }

        $homeSpread = ($this->team_id == $this->game->hometeam->id) ? $this->spread : $this->opponentSpread();

        if(($this->game->home_score + $homeSpread) > $this->game->away_score){
            return ($this->team_id == $this->game->homeTeam->id) ? $this->user : $this->opponent;
        } else {
            return ($this->team_id == $this->game->awayTeam->id) ? $this->user : $this->opponent;
        }
    }
}
