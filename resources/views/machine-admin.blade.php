@extends('layouts.app')

@section('content')
    <div class="container">
        <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
        @foreach($teams as $team)
            @if(!$team['tweets'])
                <p>No tweets to check.</p>
            @endif
            @foreach($team['tweets'] as $tweet)
                {!! Form::open(['url' => 'tweets/'.$tweet['id'].'/save', 'method' => 'post']) !!}
                <blockquote class="twitter-video" data-lang="en">
                    <a href="https://twitter.com/{{ $team['twitter'] }}/status/{{ $tweet['id'] }}"></a>
                </blockquote>
                {!! Form::hidden('league', $team['league']) !!}
                {!! Form::button('Good', array('type' => 'submit', 'name' => 'goodImage', 'class' => 'btn btn-success'))  !!}
                {!! Form::button('Bad', array('type' => 'submit', 'name' => 'badImage', 'class' => 'btn btn-danger'))  !!}
                {!! Form::close() !!}
            @endforeach
        @endforeach
    </div>
@endsection