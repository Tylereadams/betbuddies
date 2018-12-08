@extends('layouts.app')

@section('content')
<div class="container">

    <div class="text-center text-secondary h5 pt-3">
        <a href="{{ url('games', ['date' => $yesterday]) }}" class="text-secondary"><i class="fas fa-arrow-left"></i></a>
         <span class="text-muted">{{ $date }}</span>
        <a href="{{ url('games', ['date' => $tomorrow]) }}" class="text-secondary"><i class="fas fa-arrow-right"></i></a>
    </div>

    @if(isset($gamesByLeague) && count($gamesByLeague))
        @foreach($gamesByLeague as $league => $games)
                <table class="table table-borderless table-condensed table-hover">
                    <thead>
                        <tr>
                            <th colspan="4"><h5>{{ strtoupper($league) }}</h5></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($games as $key => $game)
                        <tr class='clickable link-row' data-href="{{ url('game/'.$game['urlSegment']) }}">
                            <td>
                                <img src="{{ $game['homeTeam']['thumbUrl'] }}" class="avatar">&nbsp;<span class="{{ $game['homeTeam']['isWinner'] ? 'font-weight-bold' : '' }}">{{ $game['homeTeam']['name'] }}</span> @if($game['homeTeam']['isWinner'])<i class="fas fa-caret-left"></i>@endif @if($game['homeTeam']['betCount']) ({{ $game['homeTeam']['betCount'] }})@endif<br>
                                <img src="{{ $game['awayTeam']['thumbUrl'] }}" class="avatar">&nbsp;<span class="{{ $game['awayTeam']['isWinner'] ? 'font-weight-bold' : '' }}">{{ $game['awayTeam']['name'] }}</span> @if($game['awayTeam']['isWinner'])<i class="fas fa-caret-left"></i>@endif @if($game['awayTeam']['betCount']) ({{ $game['awayTeam']['betCount'] }})@endif
                            </td>
                            <td class="align-middle text-right">
                                @if($game['status'] == 'in progress' || $game['status'] == 'ended')
                                        <span class="{{ $game['homeTeam']['isWinner'] ? 'font-weight-bold' : '' }}">{{ $game['homeTeam']['score'] }}</span><br>
                                        <span class="{{ $game['awayTeam']['isWinner'] ? 'font-weight-bold' : '' }}">{{ $game['awayTeam']['score'] }}</span>
                                @endif
                            </td>
                            <td class="align-middle text-right">
                                @if($game['status'] == 'in progress' && $game['period'])
                                    {{ $game['period'] }}
                                @elseif($game['endedAt'])
                                    <strong>F</strong>
                                @elseif($game['status'] == 'postponed')
                                    Postponed
                                @else
                                    {{ $game['startTime'] }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
        @endforeach
    @else
        <div class="text-center">
            <p>No games today.</p>
        </div>
    @endif
</div>
@endsection

@section('modal')



@endsection