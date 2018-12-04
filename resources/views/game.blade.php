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
                <div class="text-center">
                    @if($game['status'] == 'in progress' && $game['period'])
                            <h4>{{ $game['period'] }} {{ $game['league']['periodLabel'] }}</h4>
                    @elseif($game['status'] == 'postponed')
                            <h4>Postponed</h4>
                    @elseif($game['endedAt'])
                            <h4>Final</h4>
                    @endif
                </div>
            </div>
            <div class="col-sm-12">
                <i class="far fa-calendar-alt"></i> {{ $game['startDate'] }} {{ $game['startTime'] }}<br>
                @if($game['broadcast'])
                    <i class="fas fa-tv"></i> {{ $game['broadcast'] }}
                @endif
            </div>
        </div>

        <div class="row pb-4">
            @foreach($tweetsToEmbed as $tweet)

                @include('partials.embeddedTweet', $tweet)

            @endforeach
        </div>

        <div row="pb-4">
            <bets-list bets="{{ json_encode($game['bets']) }}"></bets-list>
        </div>

    </div>

@endsection

{{--@section('modal')--}}

    {{--@foreach($bets as $bet)--}}
        {{--@if($bet['isAcceptable'] && $bet['fromMe'])--}}
            {{-- Modal --}}
            {{--@include('partials.deleteBetModal', ['bet'=> $bet])--}}
        {{--@endif--}}

        {{--@if($bet['isAcceptable'] && !$bet['fromMe'])--}}
            {{-- Modal --}}
            {{--@include('partials.acceptBetModal', ['bet' => $bet])--}}
        {{--@endif--}}
    {{--@endforeach--}}

{{--@endsection--}}

@section('scripts')
    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
@endsection
