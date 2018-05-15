@extends('layouts.app')

@section('content')
    <div class="container">
        <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
            @foreach($tweets as $tweet)
                {!! Form::open(['url' => 'tweets/'.$tweet->tweet_id.'/save', 'method' => 'post']) !!}
                <blockquote class="twitter-video" data-lang="en">
                    <a href="https://twitter.com/{{ $tweet->team->twitter }}/status/{{ $tweet->tweet_id }}"></a>
                </blockquote>
                {!! Form::hidden('league', $tweet->team->league_id) !!}
                {!! Form::button('Good', array('type' => 'submit', 'name' => 'goodImage', 'class' => 'btn btn-success'))  !!}
                {!! Form::button('Bad', array('type' => 'submit', 'name' => 'badImage', 'class' => 'btn btn-danger'))  !!}
                {!! Form::close() !!}
            @endforeach
    </div>
@endsection