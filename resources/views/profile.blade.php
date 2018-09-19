@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row my-2">
        <div class="col-lg-12 order-lg-2">
            <div class="tab-content py-4">
                <div class="tab-pane active" id="profile">
                    <div class="row">
                        <div class="col-4 col-sm-2 text-center">
                            <h6>Winnings</h6>
                            <p>${{ $winnings }}</p>
                        </div>
                        <div class="col-4 col-sm-2 text-center">
                            <h6>Won</h6>
                            <p>{{ $betsWon }}</p>
                        </div>
                        <div class="col-4 col-sm-2 text-center">
                            <h6>Lost</h6>
                            <p>{{ $betsLost }}</p>
                        </div>
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
                                <div class="jumbotron jumbotron-fluid">
                                    <div class="text-center">
                                        <a href="{{ route('games') }}">
                                            <button class="btn btn-primary btn-large">Add a bet</button>
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
