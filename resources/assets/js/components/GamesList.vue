<template>
    <b-container class="p-2">
            <b-tabs>
                <div v-for="(leagueGames, key) in gamesList">
                    <b-tab>
                        <template slot="title">
                            <b-nav-item>
                                {{ key.toUpperCase() }}
                            </b-nav-item>
                        </template>

                            <div class='row pt-3' v-on:click="goToGame(game.urlSegment)" v-for="(game, key) in leagueGames">
                                <div class="col-xs-6">
                                    <img :src="game.awayTeam.thumbUrl" class="avatar">&nbsp;<span :class="game.awayTeam.isWinner ? 'font-weight-bold' : ''">{{ game.awayTeam.name }}</span> <span v-if="game.awayTeam.isWinner"><i class="fas fa-caret-left"></i></span><span v-if="game.awayTeam.betCount">({{ game.awayTeam.betCount }})</span><br>
                                    <img :src="game.homeTeam.thumbUrl" class="avatar">&nbsp;<span :class="game.homeTeam.isWinner ? 'font-weight-bold' : ''">{{ game.homeTeam.name }}</span> <span v-if="game.homeTeam.isWinner"><i class="fas fa-caret-left"></i></span><span v-if="game.homeTeam.betCount"> ({{ game.homeTeam.betCount }})</span>
                                </div>
                                <div class="col-xs-3">
                                    <!-- In progress games-->
                                    <span v-if="game.status == 'in progress' || game.status == 'ended'">
                                        <span :class="game.awayTeam.isWinner ? 'font-weight-bold' : ''">{{ game.awayTeam.score }}</span><br>
                                        <span :class="game.homeTeam.isWinner ? 'font-weight-bold' : ''">{{ game.homeTeam.score }}</span>
                                    </span>
                                </div>
                                <div class="col-xs-3">
                                    <span v-if="game.status == 'upcoming'">{{ game.startTime }}</span>
                                    <span v-if="game.endedAt"><strong>Final</strong></span>
                                    <span v-if="game.status == 'in progress' && game.period">{{ game.period }}</span>
                                    <span v-if="game.status == 'postponed'">Postponed</span>
                                </div>
                            </div>
                    </b-tab>
                </div>
            </b-tabs>

    </b-container>
</template>

<script type="text/babel">
    export default {
        props: [
            'gamesByLeague',
        ],
        data: function () {
            return {
                gamesList: this.gamesByLeague,
               // isLoading: false
            }
        },
        watch: {
            gamesByLeague: function(games) { // watch it
                var self = this;
                self.gamesList = games;
            }
        },
        methods: {
        //     refreshGames: function(date){
        //         var self = this;
        //
        //         self.isLoading = true;
        //
        //         axios.get('/api/games/' + self.date).then(response => {
        //             self.gamesList = response.data.gamesByLeague;
        //             self.isLoading = false;
        //         });
        //     },
        //     goToGame: function(urlSegment){
        //         window.location.href = '/game/' + urlSegment;
        //     }
        // },
        // mounted: function () {
        //     setInterval(function () {
        //         this.refreshGames(self.date);
        //     }.bind(this), 5 * 60000); // every 5 minutes update the scores
        }
    }
</script>
