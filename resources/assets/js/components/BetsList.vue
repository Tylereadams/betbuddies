<template>
    <b-container>

        @if($game['isBettable'])
        @include('partials.createBetModal')
        @else
        <div class="scrolling-wrapper">
            @foreach($tweetsToEmbed as $tweet)

            @include('partials.embeddedTweet', $tweet)

            @endforeach
        </div>
        @endif
        @if(count($bets) || $game['isBettable'])
        <div class="row">
            <div class="col">
                <div class="table-responsive">
                    <table class="table table-borderless table-condensed table-hover">
                        <thead>
                        <tr>
                            <th colspan="4" data-toggle="modal" data-target="#createBetModal">
                                Bets ({{ count($bets) }})
                                @if($game['isBettable'])
                                <!-- Button trigger modal -->
                                <a class="clickable text-primary">
                                    <i class="fas fa-plus"></i>
                                </a>
                                @endif
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($bets as $bet)
                        @include('partials.bet-row')
                        @endforeach
                        </tbody>
                    </table>
                    @if(!count($bets) && $game['isBettable'])
                    <div class="jumbotron jumbotron-fluid">
                        <div class="text-center">
                            @guest
                            <a href="login" class="btn btn-light btn-large" disabled>Login to Bet</a>
                            @endguest
                            @auth
                            <button class="btn btn-primary btn-large " data-toggle="modal" data-target="#createBetModal">Add a bet</button>
                            @endauth
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </b-container>
</template>

<script type="text/babel">
    export default {
        props: [
        ],
        data: function () {
            return {
            }
        },
        methods: {
            refreshBets: function(){
                var self = this;

                self.isLoading = true;

                axios.get('/api/games/' + self.date).then(response => {
                    self.betList = response.data.gamesByLeague;
                    self.isLoading = false;
                });
            }
        },
        mounted: function () {
            // setInterval(function () {
            //     this.refreshGames();
            // }.bind(this), 5 * 60000); // every 5 minutes update the scores
        }
    }
</script>
