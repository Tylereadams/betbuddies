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
                        <tr class='clickable link-row {{ $key > 4 ? 'collapse multi-collapse' : '' }}' data-href="{{ url('game/'.$game['urlSegment']) }}">
                            <td>
                                <img src="{{ $game['homeTeam']['thumbUrl'] }}" class="avatar">&nbsp;<span class="{{ $game['homeTeam']['isWinner'] ? 'font-weight-bold' : '' }}">{{ $game['homeTeam']['name'] }}</span><br>
                                <img src="{{ $game['awayTeam']['thumbUrl'] }}" class="avatar">&nbsp;<span class="{{ $game['awayTeam']['isWinner'] ? 'font-weight-bold' : '' }}">{{ $game['awayTeam']['name'] }}</span>
                            </td>
                            <td class="align-middle text-center">
                                @if($game['status'] == 'upcoming')
                                    {{ $game['startTime'] }}
                                @endif
                                @if($game['status'] == 'in progress' || $game['status'] == 'ended')
                                        <span class="{{ $game['homeTeam']['isWinner'] ? 'font-weight-bold' : '' }}">{{ $game['homeTeam']['score'] }}</span><br>
                                        <span class="{{ $game['awayTeam']['isWinner'] ? 'font-weight-bold' : '' }}">{{ $game['awayTeam']['score'] }}</span>
                                @endif
                            </td>
{{--                            @if(!$game['status'] == 'upcoming')--}}
                            <td class="align-middle text-center">
                                @if($game['endedAt'])
                                    Final
                                @elseif($game['status'] == 'in progress' && $game['period'])
                                    {{ $game['period'] }}
                                @elseif($game['status'] == 'postponed')
                                    Postponed
                                @endif
                            </td>
                            {{--@endif--}}
                        </tr>
                        @if($loop->last)

                        @endif
                    @endforeach
                    </tbody>
                </table>
            @if(count($games) > 4)
                {{-- TODO: Toggle the icon, show/hide. Right now show all only shows --}}
                <button class="btn btn-primary btn-small" type="button" data-toggle="collapse" data-target=".multi-collapse">View all</button>
            @endif
        @endforeach
    @else
        <div class="text-center">
            <p>No games today.</p>
        </div>
    @endif
</div>
@endsection