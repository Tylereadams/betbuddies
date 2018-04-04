@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row text-center">
            <div class="col">
                <img src="{{ $game['homeTeam']['thumbUrl'] }}" class="avatar"><br>
                {{ $game['homeTeam']['name'] }}
            </div>
            <div class="col text-center">
                <img src="{{ $game['awayTeam']['thumbUrl'] }}" class="avatar"><br>
                {{ $game['awayTeam']['name'] }}
            </div>
        </div>

        <div class="row">
            <div class="col text-center">
                <p>{{ $game['startDate'] }} {{ $game['startTime'] }}</p>
            </div>
        </div>

        {{--<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#addBetSection" aria-expanded="false">--}}
            {{--Add Bet--}}
        {{--</button>--}}

        {{-- Add Bet Form--}}
        <div id="addBetSection">
            {!! Form::open(['route' => 'game/'.$game['urlSegment'], 'method' => 'post']) !!}
            <div class="row">
                <div class="col">
                    {!! Form::label('teamId', 'Team') !!}
                    {!! Form::select('teamId', [$game['homeTeam']['id'] => $game['homeTeam']['name'], $game['awayTeam']['id'] => $game['awayTeam']['name']], null, ['placeholder' => 'Pick a team...', 'class' => 'form-control']) !!}
                </div>
                <div class="col">
                    {!! Form::label('amount', 'Amount', ['class' => 'control-label']) !!}
                    {!! Form::number('amount', 'Amount', ['min' => 1, 'max' => 100, 'class' => 'form-control', 'placeholder' => '$' ]) !!}
                </div>
                <div class="col">
                    {!! Form::label('spread', 'Spread') !!}
                    {!! Form::number('spread', 'Spread',  ['min' => -20, 'max' => 20, 'step' => 0.5, 'class' => 'form-control', 'placeholder' => '+/-' ]) !!}
                </div>
            </div>
            <div class="row p-3">
                {!! Form::hidden('gameId', $game['id']) !!}
                {!! Form::submit('Add Bet', array('class' => 'btn btn-primary')) !!}
            </div>
            {!! Form::close() !!}
        </div>

        @if($bets)
        <div class="row container">
            <div class="col">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th colspan="3">Bets</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($bets as $bet)
                            <tr>
                                <td>{{ $bet['team']['name'] }} {{ formatSpread($bet['spread']) }}</td>
                                <td>${{ $bet['amount'] }}</td>
                                <td>
                                    {!! Form::open(['route' => ['bet.accept', $bet['id']], 'method' => 'post']) !!}
                                    {!! Form::hidden('betId', $bet['id']) !!}
                                    {!! Form::submit('Accept', array('class' => 'btn btn-success btn-sm')) !!}
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