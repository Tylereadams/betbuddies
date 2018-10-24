@extends('layouts.app')

@section('content')
    <div class="container">
        {{--<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>--}}
            @foreach($tweets as $tweet)
                <div class="card" style="width: 18rem;">
                    <img class="card-img-top" src="{{ $tweet['imageUrl'] }}" alt="Card image cap">
                    <div class="card-body">
                            <h5 class="card-title">{{ $tweet['team']['twitter'] }}</h5>
                        <a href="{{ $tweet['tweetUrl'] }}" target="_blank">
                            <p class="card-text">{{ $tweet['text'] }}</p>
                        </a>
                        {{--<a href="#" class="btn btn-primary">Go somewhere</a>--}}
                    </div>
                </div>
                {{--<blockquote class="twitter-video" data-lang="en">--}}
                    {{--<a href="https://twitter.com/{{ $tweet['team']['twitter'] }}/status/{{ $tweet['id'] }}"></a>--}}
                {{--</blockquote>--}}
            @endforeach
    </div>
@endsection