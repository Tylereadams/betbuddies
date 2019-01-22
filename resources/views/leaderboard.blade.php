@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">

                    <h2 class="pt-2">Leaderboard</h2>
                    <hr>

                    <div class="panel-body">
                        <table class="table table-sm table-hover table-borderless">
                            <thead>
                            <tr class="text-center">
                                <th scope="col"></th>
                                <th scope="col">W</th>
                                <th scope="col">L</th>
                                <th scope="col">%</th>
                                <th scope="col">$</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($stats as $stat)
                                <tr>
                                    <td>{{ $stat['name'] }}</td>
                                    <td class="text-center">{{ $stat['wins'] }}</td>
                                    <td class="text-center">{{ $stat['losses'] }}</td>
                                    <td class="text-center">{{ $stat['win_percentage'] }}</td>
                                    <td class="text-center">${{ $stat['winnings'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
