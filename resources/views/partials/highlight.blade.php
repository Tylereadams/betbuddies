<video width="100%" height="240" poster="{{ $imageUrl }}" controls>
    <source src="{{ $highlightUrl }}" type="video/mp4">
    Your browser does not support the video tag.
</video>
@if($players->isNotEmpty())
    <label>
        <i class="fas fa-user-circle"></i>
        @foreach($players as $player)
            {{ $player['name'] }}@if(!$loop->last) / @endif
        @endforeach
    </label>
@endif