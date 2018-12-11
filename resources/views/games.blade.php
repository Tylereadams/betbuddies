@extends('layouts.app')

@section('content')
<div class="container">

    <div class="text-center h5">
        <a href="{{ url('games', ['date' => $yesterday]) }}" class="text-secondary"><i class="fas fa-arrow-left"></i></a>
         <span class="text-muted">{{ $date }}</span>
        <a href="{{ url('games', ['date' => $tomorrow]) }}" class="text-secondary"><i class="fas fa-arrow-right"></i></a>
    </div>

    <games-list v-bind:games-by-league="{{ json_encode($gamesByLeague) }}" v-bind:date="{{ json_encode($urlDate) }}"></games-list>

</div>
@endsection

@section('modal')
@endsection

@section('scripts')
@endsection