<b-card no-body class="mt-2">
    <video width="100%" height="240" poster="{{ $imageUrl }}" controls>
        <source src="{{ $highlightUrl }}" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    @if($players->isNotEmpty() || $period)
        <b-card-footer>
            <b-row class="text-center">
                <b-col>
                    @foreach($players as $player)
                        {{ $player['name'] }}@if(!$loop->last) / @endif
                    @endforeach
                </b-col>
            </b-row>
        </b-card-footer>
    @endif

</b-card>