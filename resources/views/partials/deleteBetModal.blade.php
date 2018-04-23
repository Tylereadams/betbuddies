<!-- Modal -->
<div class="modal fade" id="deleteBetModal{{ $bet['id'] }}" tabindex="-1" role="dialog" aria-labelledby="deleteBetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteBetModalLabel">Delete your bet?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => ['bets.delete', $bet['id']], 'method' => 'delete']) !!}
                {!! Form::hidden('betId', $bet['id']) !!}
                <div class="row">
                    <div class="col">
                        ${{ $bet['amount'] }}
                        @if($bet['isHome'])
                            {{ $bet['game']['homeTeam']['name'] }}
                        @else
                            {{ $bet['game']['awayTeam']['name'] }}
                        @endif
                        {{ formatSpread($bet['spread']) }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                {!! Form::hidden('gameId', $bet['game']['id']) !!}
                {!! Form::button('Delete', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>