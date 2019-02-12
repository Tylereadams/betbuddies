@extends('layouts.app')

@section('header')

@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">

                    <div class="row pt-2">
                        <div class="col-12">
                            <div class="form-group col-4">
                                <label for="username" class="font-weight-bold">Change Team</label>
                                <select class="form-control" id="teamSelect">
                                    @foreach($teams as $teamId => $team)
                                        <option value="{{ $team->id }}" @if($selectedTeam['id'] == $team->id) selected @endif>{{ $team->nickname }} {{ $team->league->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <h2 class="pt-2">Edit Team - @if($selectedTeam){{ $selectedTeam['nickname'] }}@endif</h2>
                        </div>
                    </div>
                    <hr>

                    <div class="panel-body">
                        <form action="{{ url('team/'.$selectedTeam['id'].'/edit') }}" method="POST">
                            {{ csrf_field() }}
                            {{--Selected Team--}}
                            <div class="row">
                                <div class="form-group col-sm-10">
                                    <label for="username" class="font-weight-bold">Username</label>
                                    <input type="text"  class="form-control" id="username" name="username" value="{{ $selectedTeam['username'] }}">
                                </div>

                                <div class="form-group col-sm-10">
                                    <label for="name" class="font-weight-bold">Profile Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ $selectedTeam['name'] }}">
                                </div>

                                <div class="form-group col-sm-10">
                                    <label for="email" class="font-weight-bold">Email</label>
                                    <input type="text" class="form-control" id="email" name="email" value="{{ $selectedTeam['email'] }}">
                                </div>

                                <div class="form-group col-sm-10">
                                    <label for="description" class="font-weight-bold">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" value="{{ $selectedTeam['location'] }}">
                                </div>

                                <div class="form-group col-sm-10">
                                    <label for="description" class="font-weight-bold">Description</label>
                                    <textarea class="form-control" rows="3" id="description" name="description">{{ $selectedTeam['description'] }}</textarea>
                                </div>

                                <div class="form-group col-sm-10">
                                    <label for="token" class="font-weight-bold">Token</label>
                                    <input type="text" readonly class="form-control-plaintext" id="token" name="token" value="{{ $selectedTeam['token'] }}">
                                </div>

                                <div class="form-group col-sm-10">
                                    <label for="tokenSecret" class="font-weight-bold">Token Secret</label>
                                    <input type="text" readonly class="form-control-plaintext" id="tokenSecret" name="tokenSecret" value="{{ $selectedTeam['tokenSecret'] }}">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save</button>
                            <a class="btn btn-primary" href="/login/twitter">Authorize with Twitter</a>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        @if(isset($selectedTeam['username']))
            <hr>
            <div class="row">
                <div class="panel-body">
                    <a class="twitter-timeline" href="https://twitter.com/{{ $selectedTeam['username'] }}?ref_src=twsrc%5Etfw"></a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
                </div>
            </div>
        @endif
    </div>
@endsection


@section('scripts')
    <script>
        $('#teamSelect').on('change',function(){
            var value = $(this).val();
            location.href = '/team/edit?teamId=' + value;
        });
    </script>
@endsection