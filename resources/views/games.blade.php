@extends('layouts.app')

@section('content')
    <div class="container">
        <small class="text-muted">{{ $date }}</small>
        @if(count($gamesByLeague))
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    @foreach(array_keys($gamesByLeague) as $key => $league)
                        <a class="nav-item nav-link {{ $selectedLeague == $league ? 'active' : '' }}" id="nav-{{ $league }}-tab" data-toggle="tab" href="#nav-{{ $league }}" role="tab" aria-controls="nav-{{ $league }}" aria-selected="true">{{ $league }}</a>
                    @endforeach
                </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">
                @foreach($gamesByLeague as $league => $games)
                    <div class="tab-pane fade table-responsive {{ $selectedLeague == $league ? 'show active' : '' }}" id="nav-{{ $league }}" role="tabpanel" aria-labelledby="nav-{{ $league }}-tab">
                        <table class="table">
                            <tbody>
                            @foreach($games as $game)
                                <tr>
                                    <td>
                                        <a href="{{ url('game/'.$game['urlSegment']) }}">
                                            <img src="{{ $game['homeTeam']['thumbUrl'] }}" class="avatar">&nbsp;{{ $game['homeTeam']['name'] }}<br>
                                            <img src="{{ $game['awayTeam']['thumbUrl'] }}" class="avatar">&nbsp;{{ $game['awayTeam']['name'] }}
                                        </a>
                                    </td>
                                    @if($game['status'] != 'locked')
                                    <td class="align-middle">
                                        {{ $game['startTime'] }}
                                    </td>
                                    @else
                                    <td class="align-middle">
                                        {{ $game['homeTeam']['score'] }}<br>
                                        {{ $game['awayTeam']['score'] }}
                                    </td>
                                    @endif

                                    @if($game['endedAt'])
                                        <td class="align-middle">Final</td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        @else
            <div>
                <p>No games today.</p>
            </div>
        @endif
    </div>
@endsection