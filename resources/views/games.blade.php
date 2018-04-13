@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="p-2">
            <a href="{{ url('games', ['date' => $yesterday]) }}"><i class="fas fa-angle-left"></i></a>
            <small class="text-muted">{{ $date }}</small>
            <a href="{{ url('games', ['date' => $tomorrow]) }}"><i class="fas fa-angle-right"></i></a>
        </div>

    @if(count($gamesByLeague))
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    @foreach(array_keys($gamesByLeague) as $key => $league)
                        <a class="nav-item nav-link {{ $selectedLeague == $league ? 'active' : '' }}" id="nav-{{ $league }}-tab" data-toggle="tab" href="#nav-{{ $league }}" role="tab" aria-controls="nav-{{ $league }}" aria-selected="true">{{ strtoupper($league) }}</a>
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
                                    <td class="align-middle">
                                        @if($game['status'] == 'upcoming')
                                            {{ $game['startTime'] }}
                                        @endif
                                        @if($game['status'] == 'in progress' || $game['status'] == 'ended')
                                           {{ $game['homeTeam']['score'] }}<br>
                                           {{ $game['awayTeam']['score'] }}
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($game['status'] == 'ended')
                                            Final {{ $game['startTime'] }}
                                        @elseif($game['status'] == 'in progress' && $game['period'])
                                            {{ $game['period'] }}
                                        @endif
                                    </td>
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