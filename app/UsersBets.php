<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

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

    public function opponentSpread()
    {
        return ($this->opponentTeam->id == $this->game->homeTeam->id ? $this->game->home_spread : $this->game->away_spread);
    }


    public function getWinnerData()
    {
        if(!$this->opponent || !$this->game->ended_at)
        {
            return '';
        }

        $userScore = ($this->team->id == $this->game->homeTeam->id ? $this->game->home_score : $this->game->away_score);
        $opponentScore = ($this->opponentTeam->id == $this->game->homeTeam->id ? $this->game->home_score : $this->game->away_score);

        $winner = ($userScore + $this->spread > $opponentScore ? $this->user : $this->opponent);

        $winnerData = [
            'avatarUrl' => $winner->avatar,
            'name' => $winner->first_name
        ];

        return $winnerData;
    }


    public function getCardData()
    {
//        $startDate = Carbon::parse($this->game->start_date);
        $opponentTeam = $this->getOpponentTeam();

        $betData = [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatarUrl' => $this->user->avatarUrl,
//                'homeOrAway' => ($this->team->id == $this->game->homeTeam->id ? 'home' : 'away')
            ],
            'amount' => (int) $this->amount,
            'spread' => (float) $this->spread,
            'team' => [
                'id' => $this->team->id,
                'name' => $this->team->nickname,
                'logoUrl' => $this->team->logoUrl()
            ],
            'opponent' => [
                'team' => [
                    'name' => $opponentTeam->nickname
                ],
                'spread' => $this->spread > 0 ? $this->spread * -1 : abs($this->spread)
            ]
//            'homeTeam' => [
//                'id' => $this->game->homeTeam->id,
//                'name' => $this->game->homeTeam->nickname,
//                'spread' => $this->game->homeTeam->spread,
//                'logoUrl' => $this->game->homeTeam->logoUrl()
//            ],
//            'awayTeam' => [
//                'id' => $this->game->awayTeam->id,
//                'name' => $this->game->awayTeam->nickname,
//                'spread' => $this->game->awayTeam->spread,
//                'logoUrl' => $this->game->awayTeam->logoUrl()
//            ],
//            'period' => $this->game->period,
//            'periodLabel' => $this->game->league->period_label,
//            'league' => $this->game->league->name,
//            'location' => $this->game->homeTeam->location,
//            'humanDate' => ($startDate->timestamp < strtotime('+1 hour')  ? $startDate->diffForHumans() : $startDate->format('M j')),
//            'startTime' => $startDate->format('g:ia'),
        ];

//        if($this->opponent) {
//            $betData['opponent'] = [
//                'id' => $this->opponent->id,
//                'name' => $this->opponent->name,
//                'avatarUrl' => $this->opponent->avatarUrl,
//                'team' => [
//                    'id' => $this->team->id,
//                    'name' => $this->team->nickname,
//                    'logoUrl' => $this->team->logoUrl()
//                ]
//                'homeOrAway' => ($this->team->id == $this->game->homeTeam->id ? 'home' : 'away')
//            ];
//        }

        return $betData;
    }

    public function getOpponentTeam()
    {
        return $this->game->homeTeam->id == $this->team->id ? $this->game->awayTeam : $this->game->homeTeam;
    }

}
