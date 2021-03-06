<!-- Modal -->
<div class="modal fade" id="acceptBetModal-{{ $bet['id'] }}" tabindex="-1" role="dialog" aria-labelledby="acceptBetModalLabel-{{ $bet['id'] }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="acceptBetModal-{{ $bet['id'] }}">Accept Bet?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => ['bets.accept', $bet['id']], 'method' => 'post']) !!}
                {!! Form::hidden('betId', $bet['id']) !!}
                ${{ $bet['amount'] }}
                {{ $bet['isHome'] ? $bet['game']['awayTeam']['name'] : $bet['game']['homeTeam']['name'] }} {{ $bet['spread'] * -1 }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                {!! Form::hidden('gameId', $bet['game']['id']) !!}
                {!! Form::button('Submit', array('type' => 'submit', 'class' => 'btn btn-primary')) !!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>