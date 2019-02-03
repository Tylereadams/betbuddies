@extends('layouts.app')

@section('header')

@endsection

@section('content')
    <div class="position-relative overflow-hidden p-3 p-md-5 m-md-3 text-center">
        <div class="col-md-12 mx-auto">
            <h1 class="display-4 pt-4">
                <a href="{{ url('/games') }}">
                    <i class="fas fa-users"></i><br>
                    BetBuddies
                </a>
            </h1>
            <p class="lead">A <span class="font-weight-bold">free</span> and <span  class="font-weight-bold">easy</span> way to create custom bets with your buddies. We don't even need a real email.</p>

            <div class="row pt-3">
                <div class="col">
                    <a class="btn btn-outline-primary btn-lg  align-middle" href="/games">Search Games <i class="fas fa-angle-right"></i></a>
                </div>
            </div>

            <br>
            <hr>

            <b-row class="row text-center font-weight-light text-secondary pt-4">
                <b-col>
                    <h2><i class="fas fa-dollar-sign"></i></h2>
                    <p>100% Free</p>
                </b-col>
                <b-col>
                    <h2><i class="fas fa-sort-numeric-up"></i></h2>
                    <p>Custom spreads</p>
                </b-col>
                <b-col>
                    <h2><i class="fas fa-user-friends"></i></h2>
                    <p>Peer to Peer</p>
                </b-col>
            </b-row>
        </div>
    </div>

    <div class="bg-light d-md-flex flex-md-equal w-100 my-md-3 pl-md-3 overflow-hidden" style="overflow: hidden;">
        <img src="/img/iphone_screenshot.png" class="w-50-xs bg-light">
    </div>

@endsection
