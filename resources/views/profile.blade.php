@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">

                <h2 class="pt-2"><i class="fas fa-user-circle fa-1x text-muted"></i> {{ $user['name'] }}</h2>
                <hr>

            <div class="row jumbotron jumbotron-fluid bg-light">
                <div class="col-4 col-sm-4 text-center">
                    <h4>Record</h4>
                    <h5>{{ $wins }} - {{ $losses }}</h5>
                </div>
                <div class="col-4 col-sm-4 text-center">
                    <h4>Win %</h4>
                    <h5>{{ number_format($winPercentage, 3) }}</h5>
                </div>
                <div class="col-4 col-sm-4 text-center">
                    <h4>Winnings</h4>
                    <h5>${{ $winnings }}</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h4 class="mt-2"><span class="fa fa-clock-o ion-clock float-right"></span> Completed Bets</h4>

                    @if($bets)
                        <table class="table table-condensed table-borderless table-hover">
                            <tbody>
                            @foreach($bets as $bet)
                                @include('partials.bet-row')
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="jumbotron jumbotron-fluid" bg-variant="light">
                            <div class="text-center">
                                <a href="{{ route('games') }}">
                                    <button class="btn btn-primary btn-large">Find a game to bet on <i class="fas fa-arrow-circle-right"></i></button>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')

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

@endsection
