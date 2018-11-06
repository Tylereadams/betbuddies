@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="row text-center game-teams__header">
            <div class="col">
                <img src="{{ $game['homeTeam']['thumbUrl'] }}" class="avatar-lg"><br>
                {{ $game['homeTeam']['name'] }}
                <h3>{{ $game['homeTeam']['score'] }}</h3>
            </div>
            <div class="col">
                <img src="{{ $game['awayTeam']['thumbUrl'] }}" class="avatar-lg"><br>
                {{ $game['awayTeam']['name'] }}
                <h3>{{ $game['awayTeam']['score'] }}</h3>
            </div>
        </div>

        <div class="row pb-4">
            <div class="col-sm-12">
                @if($game['status'] == 'in progress' && $game['period'])
                    <div class="text-center">
                        <h4>{{ $game['period'] }} {{ $game['league']['periodLabel'] }}</h4>
                    </div>
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
            <div class="col-sm-12">
                <i class="far fa-calendar-alt"></i> {{ $game['startDate'] }} {{ $game['startTime'] }}<br>
                @if($game['broadcast'])
                    <i class="fas fa-tv"></i> {{ $game['broadcast'] }}
                @endif
            </div>
        </div>

        @if($game['isBettable'])
            @include('partials.createBetModal')
        @else
            <div class="scrolling-wrapper">
                <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
                @foreach($tweetsToEmbed as $tweet)

                    @include('partials.embeddedTweet', $tweet)

                @endforeach
            </div>
        @endif
        @if(count($bets) || $game['isBettable'])
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
                                @guest
                                    <a href="{{ url('login') }}" class="btn btn-light btn-large" disabled>Login to Bet</a>
                                @endguest
                                @auth
                                    <button class="btn btn-primary btn-large " data-toggle="modal" data-target="#createBetModal">Add a bet</button>
                                @endauth
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
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
