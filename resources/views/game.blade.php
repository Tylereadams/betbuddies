@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="row text-center game-teams__header">
            <div class="col">
                <img src="{{ $game['homeTeam']['thumbUrl'] }}" class="avatar-lg"><br>
                {{ $game['homeTeam']['name'] }}
                <p>{{ $game['homeTeam']['score'] }}</p>
            </div>
            <div class="col">
                <img src="{{ $game['awayTeam']['thumbUrl'] }}" class="avatar-lg"><br>
                {{ $game['awayTeam']['name'] }}
                <p>{{ $game['awayTeam']['score'] }}</p>
            </div>
        </div>

        <div class="row pb-4">
            <div class="col">
                @if($game['status'] == 'upcoming')
                    <i class="far fa-calendar-alt"></i> {{ $game['startDate'] }} {{ $game['startTime'] }}<br>
                    @if($game['broadcast'])
                        <i class="fas fa-tv"></i> {{ $game['broadcast'] }}<br>
                    @endif
                @elseif($game['status'] == 'in progress' && $game['period'])
                    {{ $game['period'] }} {{ $game['league']['periodLabel'] }}
                @elseif($game['status'] == 'postponed')
                    <div class="text-center">
                        <strong>Postponed</strong>
                    </div>
                @elseif($game['endedAt'])
                    <div class="text-center">
                        <strong>Final</strong>
                    </div>
                @endif
            </div>
        </div>

        @if($game['isBettable'])
            @include('partials.createBetModal')
        @else
            <div class="scrolling-wrapper">
                <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
                @foreach($tweetsToEmbed as $tweet)
                    <div class="card">
                        {!! $tweet['html'] !!}
                        <span class="pl-2">{{ ordinalNumber($tweet['period']) }} {{ $game['league']['periodLabel'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
        <div class="row">
            <div class="col">
                <div class="table-responsive">
                    <table class="table table-borderless table-condensed table-hover">
                        <thead>
                        <tr>
                            <th colspan="4" data-toggle="modal" data-target="#createBetModal">
                                Bets ({{ count($bets) }})
                                @if($game['isBettable'])
                                <!-- Button trigger modal -->
                                    <a class="clickable text-primary">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                @endif
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($bets as $bet)
                            @include('partials.bet-row')
                        @endforeach
                        </tbody>
                    </table>
                    @if(!count($bets) && $game['isBettable'])
                        <div class="jumbotron jumbotron-fluid">
                            <div class="text-center">
                                <button class="btn btn-primary btn-large " data-toggle="modal" data-target="#createBetModal">Add a bet!</button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@section('modal')

    @foreach($bets as $bet)
        @if($bet['isAcceptable'] && $bet['fromMe'])
            {{-- Modal --}}
            @include('partials.deleteBetModal', ['bet'=> $bet])
        @endif

        @if($bet['isAcceptable'] && !$bet['fromMe'])
            {{-- Modal --}}
            @include('partials.acceptBetModal', ['bet' => $bet])
        @endif
    @endforeach

@endsection
