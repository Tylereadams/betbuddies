<tr>

    {{-- Amount --}}
    <td class="align-middle text-left">
        <strong class="{{ $bet['isWinner'] ? 'text-success' : 'text-danger' }}">${{ $bet['amount'] }}</strong>
    </td>

    <td class="border-left {{ $bet['isWinner'] ? 'border-success' : '' }} {{ $bet['isLoser'] ? 'border-danger' : '' }} text-truncate link-row" data-href="{{ url('game/'.$bet['game']['urlSegment'] ) }}">
        <img src="{{ $bet['game']['awayTeam']['thumbUrl'] }}" class="avatar">&nbsp;{{ $bet['game']['awayTeam']['name'] }} <small>@if(!$bet['isHome']){{ formatSpread($bet['spread']) }}@endif</small><br>
        <img src="{{ $bet['game']['homeTeam']['thumbUrl'] }}" class="avatar">&nbsp;{{ $bet['game']['homeTeam']['name'] }} <small>@if($bet['isHome']){{ formatSpread($bet['spread']) }}@endif</small>
    </td>

    {{-- Scores --}}
    <td>
        <div class="row">
            {{ $bet['game']['awayTeam']['score'] }}<br>
            {{ $bet['game']['homeTeam']['score'] }}
        </div>
    </td>

    {{-- User Names --}}
    <td class="text-truncate link-row align-middle" data-href="{{ url('game/'.$bet['game']['urlSegment'] ) }}">
         {{--Bet created with home Team--}}
        @if($bet['isHome'])
            <small class="font-italic font-weight-light mt-2">
                @if(isset($bet['opponent']))
                    <i class="fas fa-user-circle"></i> <a href="{{ url('user/'.$bet['opponent']['urlSegment']) }}">{{ $bet['opponent']['name'] }}</a>
                    @if($bet['opponent']['isWinner'] && $bet['opponent']['isMe'])
                        &nbsp;<i class="fas fa-check text-success"></i>
                    @elseif($bet['isLoser'] && $bet['opponent']['isMe'])
                        &nbsp;<i class="fas fa-times text-danger"></i>
                    @endif
                @endif
            </small><br>
            <small class="font-italic font-weight-light mt-2"><i class="fas fa-user-circle"></i> <a href="{{ url('user/'.$bet['user']['urlSegment']) }}">{{ $bet['user']['name'] }}</a>
                @if($bet['user']['isWinner'] && $bet['user']['isMe'])
                    &nbsp;<i class="fas fa-check text-success"></i>
                @elseif($bet['isLoser'] && $bet['user']['isMe'])
                    &nbsp;<i class="fas fa-times text-danger"></i>
                @endif
            </small>
         {{--Bet created with away Team--}}
        @else
            <small class="font-italic font-weight-light mt-2"><i class="fas fa-user-circle"></i> <a href="{{ url('user/'.$bet['user']['urlSegment']) }}">{{ $bet['user']['name'] }}</a>
                @if($bet['user']['isWinner'] && $bet['user']['isMe'])
                    &nbsp;<i class="fas fa-check text-success"></i>
                @elseif($bet['isLoser'] && $bet['user']['isMe'])
                    &nbsp;<i class="fas fa-times text-danger"></i>
                @endif
            </small><br>
            <small class="font-italic font-weight-light mt-2">
                @if(isset($bet['opponent']))
                    <i class="fas fa-user-circle"></i> <a href="{{ url('user/'.$bet['opponent']['urlSegment']) }}">{{ $bet['opponent']['name'] }}</a>
                    @if($bet['opponent']['isWinner'] && $bet['opponent']['isMe'])
                        &nbsp;<i class="fas fa-check text-success"></i>
                    @elseif($bet['isLoser'] && $bet['opponent']['isMe'])
                        &nbsp;<i class="fas fa-times text-danger"></i>
                    @endif
                @endif
            </small>
            &nbsp
        @endif

        {{--vs<br>--}}
        {{--<i class="fas fa-user-circle"></i> <a href="{{ url('user/'.$bet['opponent']['urlSegment']) }}">{{ $bet['opponent']['name'] }}</a>--}}

    </td>
</tr>

@section('modal')


@endsection