@extends('layouts.app')

@section('content')

    @if($venue['photoUrl'])
        <div class="jumbotron d-lg-none d-md-none d-xl-none mb-2" style="
        background-image: url('{{ $venue['photoUrl'] }}');
        background-size: 100% 190px;
        background-repeat: no-repeat;
        min-height: 190px !important;
                position: relative;
        ">
        </div>
    @endif

    <div class="container pt-2">
        <div class="row text-center game-teams__header">
            <div class="col">
                <img src="{{ $game['awayTeam']['thumbUrl'] }}" class="avatar-lg"><br>
                {{ $game['awayTeam']['name'] }}
                <h3>{{ $game['awayTeam']['score'] }}</h3>
            </div>

            <div class="col">
                <img src="{{ $game['homeTeam']['thumbUrl'] }}" class="avatar-lg"><br>
                {{ $game['homeTeam']['name'] }}
                <h3>{{ $game['homeTeam']['score'] }}</h3>
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
        </div>

        <div>
            <i class="far fa-calendar-alt"></i> {{ $game['startDate'] }} {{ $game['startTime'] }}<br>
            @if($game['broadcast'])
                <i class="fas fa-tv"></i> {{ $game['broadcast'] }}
            @endif
        </div>

        <div row="pb-4">
            <bets-list bets="{{ json_encode($game['bets']) }}"></bets-list>
        </div>

        <div>
            @foreach($tweets as $tweet)
                @include('partials.highlight', $tweet)
            @endforeach
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
