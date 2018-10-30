@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="row input-group">
            <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-search"></i></div>
            </div>
            <select class="form-control basicAutoComplete" data-noresults-text="Nothing to see here." type="text" autocomplete="on" data-default-value="3" placeholder="{{ $searchTerm ?: 'Search by name' }}"></select>
        </div>

        <div class="m-4 pagination-sm">
            {{ $paginator->links() }}
        </div>


        @if($tweets)
            <div class="row align-middle">
                <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

                @foreach(array_chunk($tweets, 3) as $chunk)
                    <div class="row justify-content-center">
                        @foreach($chunk as $tweet)
                            <div class="col-lg-4">
                                <blockquote class="twitter-video" data-lang="en" data-conversation="none">
                                    <a href="https://twitter.com/{{ $tweet['team']['twitter'] }}/status/{{ $tweet['id'] }}"></a>
                                </blockquote>

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