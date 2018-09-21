@extends('layouts.app')

@section('content')
    <div class="container">

        <h5 class="mt-2 pb-2">Open Issues ({{ count($issues) }})</h5>

        @foreach($issues as $issue)

            <div class="row pt-4">
                <div class="col-md-8 col-md-offset-2">
                    <h6 class="mt-2 pb-2"><a href="{{ $issue['issueData']->html_url }}" target="_blank">{{ $issue['issueData']->title }}</a></h6>
                    <p>{{ $issue['issueData']->body }}</p>

                    <div>
                        @if(count($issue['sessions']))
                            Matching FullStory sessions ({{ $issue['email'] }}):
                                <ul>
                                    @foreach($issue['sessions'] as $session)
                                        <a href="{{ $session->FsUrl }}" target="_blank">{{ date('m/d/Y h:i a', $session->CreatedTime) }}</a><br>
                                    @endforeach
                                </ul>
                        @else
                            We couldn't find any matching FullStory sessions...<br>
                            @if($issue['email'])
                                Are you sure you created a login using {{ $issue['email'] }} and it's set as <a href="https://github.com/settings/profile" target="_blank">public</a> on your github account?
                            @endif
                         @endif
                    </div>
                </div>
            </div>
            <hr>
        @endforeach

    </div>
@endsection