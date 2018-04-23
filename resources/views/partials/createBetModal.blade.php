<!-- Modal -->
<div class="modal fade" id="createBetModal" tabindex="-1" role="dialog" aria-labelledby="createBetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createBetModalLabel">Add Bet</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {!! Form::open(['url' => 'game/'.$game['urlSegment'], 'method' => 'post']) !!}
                <div class="row">
                    <div class="col">
                        {!! Form::label('teamId', 'Team') !!}
                        <div class="custom-control custom-radio">
                            <input type="radio" id="homeTeamRadio" name="teamId" class="custom-control-input" value="{{ $game['homeTeam']['id'] }}" required>
                            <label class="custom-control-label" for="homeTeamRadio">{{ $game['homeTeam']['name'] }}</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="awayTeamRadio" name="teamId" class="custom-control-input" value="{{ $game['awayTeam']['id'] }}" required>
                            <label class="custom-control-label" for="awayTeamRadio">{{ $game['awayTeam']['name'] }}</label>
                        </div>
                    </div>
                    <div class="col">
                        {!! Form::label('amount', 'Amount', ['class' => 'control-label']) !!}
                        {!! Form::number('amount', 'Amount', ['min' => 1, 'max' => 100, 'class' => 'form-control bet-input__amount', 'size' => '4', 'placeholder' => '$' ]) !!}
                    </div>
                    <div class="col">
                        {!! Form::label('spread', 'Spread') !!}
                        {!! Form::number('spread', 'Spread',  ['min' => -20, 'max' => 20, 'step' => 0.5, 'class' => 'form-control bet-input__amount', 'size' => '4', 'placeholder' => '+/-' ]) !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                {!! Form::hidden('gameId', $game['id']) !!}
                {!! Form::button('Submit', array('type' => 'submit', 'class' => 'btn btn-primary')) !!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>