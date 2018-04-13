@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="p-2">
            <p>{{ $game['startDate'] }} {{ $game['startTime'] }}</p>
        </div>
        <div class="row text-center p-2">
            <div class="col">
                <img src="{{ $game['homeTeam']['thumbUrl'] }}" class="avatar"><br>
                {{ $game['homeTeam']['name'] }}
            </div>
            <div class="col text-center">
                <img src="{{ $game['awayTeam']['thumbUrl'] }}" class="avatar"><br>
                {{ $game['awayTeam']['name'] }}
            </div>
        </div>

        <div class="p-2 clickable">
            <a data-toggle="collapse" data-target="#addBetSection">
                <i class="fas fa-plus-square"></i> Create Bet
            </a>
        </div>

        {{-- Add Bet Form--}}
        <div id="addBetSection" class="collapse">
            <div>
                <div class="container">
                    {!! Form::open(['url' => 'game/'.$game['urlSegment'], 'method' => 'post']) !!}
                    <div class="row">
                        <div class="col">
                            {!! Form::label('teamId', 'Team') !!}
                            <div class="custom-control custom-radio">
                                <input type="radio" id="customRadio1" name="teamId" class="custom-control-input" value="{{ $game['homeTeam']['id'] }}">
                                <label class="custom-control-label" for="customRadio1">{{ $game['homeTeam']['name'] }}</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="customRadio2" name="teamId" class="custom-control-input" value="{{ $game['awayTeam']['id'] }}">
                                <label class="custom-control-label" for="customRadio2">{{ $game['awayTeam']['name'] }}</label>
                            </div>
                        </div>
                        <div class="col">
                            {!! Form::label('amount', 'Amount', ['class' => 'control-label']) !!}
                            {!! Form::number('amount', 'Amount', ['min' => 1, 'max' => 100, 'class' => 'form-control bet-input__amount', 'placeholder' => '$' ]) !!}
                        </div>
                        <div class="col">
                            {!! Form::label('spread', 'Spread') !!}
                            {!! Form::number('spread', 'Spread',  ['min' => -20, 'max' => 20, 'step' => 0.5, 'class' => 'form-control bet-input__amount', 'placeholder' => '+/-' ]) !!}
                        </div>
                    </div>
                    <div class="row p-3">
                    {!! Form::hidden('gameId', $game['id']) !!}
                    {!! Form::button('Submit', array('type' => 'submit', 'class' => 'btn btn-primary')) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        @if($bets)
        <div class="row">
            <div class="col">
                <div class="table-responsive">
                    <table class="table  table-condensed">
                        <thead>
                        <tr>
                            <th>Amount</th>
                            <th>Opponent</th>
                            <th>Team</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($bets as $bet)
                            <tr>
                                <td>${{ $bet['amount'] }}</td>
                                <td><i class="fas fa-user-circle"></i> {{ $bet['team']['name'] }} {{ formatSpread($bet['spread']) }}</td>
                                <td>
                                    {!! Form::open(['route' => ['bet.accept', $bet['id']], 'method' => 'post']) !!}
                                    {!! Form::hidden('betId', $bet['id']) !!}
                                    {!! Form::button('<i class="fas fa-plus-circle success"></i>', array('class' => 'btn btn-success-outline btn-sm', 'type' => 'submit')) !!}
                                    {{ $bet['opponent']['team']['name'] }} {{ formatSpread($bet['opponent']['spread']) }}
                                    {!! Form::close()  !!}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

@endsection