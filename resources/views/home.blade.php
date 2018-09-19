@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h5 class="mt-2 pb-2">
                        <span class="fa fa-clock-o ion-clock float-right"></span>
                        Recent Activity
                    </h5>
                    <table class="table table-condensed table-hover" id="bet-table">

                        <tbody>
                        @foreach($bets as $bet)
                            @include('partials.bet-row')
                        @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
