<tr>
    <td class="border-left {{ $bet['isWinner'] ? 'border-success' : '' }} {{ $bet['isLoser'] ? 'border-danger' : '' }}">${{ $bet['amount'] }}</td>
    <td class="text-truncate align-middle">
        <img src="{{ $bet['game']['awayTeam']['thumbUrl'] }}" class="avatar">&nbsp;{{ $bet['game']['awayTeam']['name'] }} <small>@if(!$bet['isHome']){{ formatSpread($bet['spread']) }}@endif</small><br>
        <img src="{{ $bet['game']['homeTeam']['thumbUrl'] }}" class="avatar">&nbsp;{{ $bet['game']['homeTeam']['name'] }} <small>@if($bet['isHome']){{ formatSpread($bet['spread']) }}@endif</small>
    </td>
    <td class="text-truncate align-middle">
        {{-- Bet created with home Team --}}
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
        {{-- Bet created with away Team --}}
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
    </td>
    <td class="align-middle">
        <div class="row">
            @if($bet['isAcceptable'] && $bet['fromMe'])
                {{-- Button trigger modal --}}
                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteBetModal{{ $bet['id'] }}">
                    Delete
                </button>
                @include('partials.deleteBetModal', ['bet'=> $bet])
            @elseif($bet['isAcceptable'] && !$bet['fromMe'])
                {{-- Modal --}}
                @include('partials.acceptBetModal')
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#acceptBetModal">
                    Accept
                </button>
            @endif
                {{ $bet['game']['awayTeam']['score'] }}<br>
                {{ $bet['game']['homeTeam']['score'] }}
        </div>
    </td>
</tr>