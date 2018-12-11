@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-search"></i></div>
            </div>
            <select class="form-control basicAutoComplete" data-noresults-text="Nothing to see here." type="text" autocomplete="on" data-default-value="3" placeholder="{{ $searchTerm ?: 'Search player name' }}"></select>
            <a href="?q=" class="input-group-append">
                    <div class="input-group-text"><i class="fas fa-times"></i></div>
            </a>
        </div>

        <ul class="nav justify-content-center p-2">
            @foreach($topRelatedPlayers as $player)
                <li class="nav-item pr-1">
                    <a href="?q={{ $player['name'] }}" class="badge badge-light">{{ $player['name'] }} ({{ $player['tweetCount'] }})</a>
                </li>
            @endforeach
        </ul>

        <div class="pt-2 pagination-sm">
            {{ $paginator->links() }}
        </div>

        @if($tweets)
            <div class="row align-middle p-2">
                @foreach(array_chunk($tweets, 3) as $chunk)
                    <div class="row justify-content-center">
                        @foreach($chunk as $tweet)
                            <div class="col-lg-4">

                                @include('partials.highlight', $tweet)

                                {{--Player mentions--}}
                                @if(isset($tweet['mentions']) && count($tweet['mentions']))
                                    <small>
                                        @foreach($tweet['mentions'] as $mention)
                                            <a href="{{ url("tweet-log?q=".$mention['name']) }}">
                                                {{ $mention['name'] }}@if(!$loop->last), @endif
                                            </a>
                                        @endforeach
                                    </small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="m-4 pagination-sm">
                {{ $paginator->links() }}
            </div>
        @else
            <div class="row justify-content-center pt-5">
                <p>No tweets to see for {{ $searchTerm }}.</p>
            </div>
        @endif

    </div>
@endsection

@section('scripts')
    <script>
        $('.basicAutoComplete').autoComplete({
            resolver: 'custom',
            formatResult: function (item) {
                return {
                    value: item.first_name + ' ' + item.last_name,
                    text: item.first_name + ' ' + item.last_name,
                    html: [ item.html ]
                };
            },
            events: {
                search: function (q, callback) {
                    // let's do a custom ajax call
                    $.ajax(
                        'playersearch',
                        {
                            data: { 'q': q}
                        }
                    ).done(function (res) {
                        callback(res)
                    });
                }
            }
        });

        $('.basicAutoComplete').on('autocomplete.select', function (evt, item) {
            window.location.href = '?q=' + item.first_name + ' ' + item.last_name;
        });
        //
        // $('.basicAutoComplete').on('keydown', function (evt, item) {
        //     if(evt.which === 13) {
        //         window.location.href = '?q=';
        //     }
        // });
    </script>
@endsection