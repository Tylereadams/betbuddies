@extends('layouts.app')

@section('content')
<div class="container">
    <b-jumbotron bg-variant="white"  border-variant="light">
        <div class="col-12">
            <h3><i class="fas fa-user-circle"></i> {{ $user['name'] }}</h3>
        </div>
    </b-jumbotron>
    <div class="row">
        <div class="col-6 col-sm-2 text-center">
            <h6>Winnings</h6>
            <p>${{ $winnings }}</p>
        </div>
        <div class="col-6 col-sm-2 text-center">
            <h6>Record</h6>
            <p>{{ $betsWon }} - {{ $betsLost }}</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h6 class="mt-2"><span class="fa fa-clock-o ion-clock float-right"></span> Recent Activity</h6>

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
