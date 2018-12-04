<template>
    <b-container>
        <span v-if="isLoading">Loading</span>
            <b-tabs no-fade>
                <div v-for="(leagueGames, key) in gamesList">
                    <b-tab>
                        <template slot="title">
                            {{ key.toUpperCase() }}
                        </template>

                        <div class='row pt-3' data-href="/game/jaguars-steelers-2018-11-18-1300" v-on:click="goToGame(game.urlSegment)" v-for="(game, key) in leagueGames">
                            <div class="col-xs-6">
                                <img :src="game.homeTeam.thumbUrl" class="avatar">&nbsp;<span :class="game.homeTeam.isWinner ? 'font-weight-bold' : ''">{{ game.homeTeam.name }}</span><span v-if="game.homeTeam.betCount"> ({{ game.homeTeam.betCount }})</span><br>
                                <img :src="game.awayTeam.thumbUrl" class="avatar">&nbsp;<span :class="game.awayTeam.isWinner ? 'font-weight-bold' : ''">{{ game.awayTeam.name }}</span><span v-if="game.awayTeam.betCount">({{ game.awayTeam.betCount }})</span>
                            </div>
                            <div class="col-xs-3">
                                <span v-if="game.status == 'upcoming'">{{ game.startTime }}</span>
                                <span v-else-if="game.status == 'in progress' || game.status == 'ended'">
                                    <span :class="game.homeTeam.isWinner ? 'font-weight-bold' : ''">{{ game.homeTeam.score }}</span><br>
                                    <span :class="game.awayTeam.isWinner ? 'font-weight-bold' : ''">{{ game.awayTeam.score }}</span>
                                </span>
                            </div>
                            <div class="col-xs-3">
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
            'date'
        ],
        data: function () {
            return {
                gamesList: this.gamesByLeague,
                isLoading: false
            }
        },
        methods: {
            refreshGames: function(date){
                var self = this;

                self.isLoading = true;

                axios.get('/api/games/' + self.date).then(response => {
                    self.gamesList = response.data.gamesByLeague;
                    self.isLoading = false;
                });
            },
            goToGame: function(urlSegment){
                window.location.href = '/game/' + urlSegment;
            }
        },
        mounted: function () {
            setInterval(function () {
                this.refreshGames(self.date);
            }.bind(this), 5 * 60000); // every 5 minutes update the scores
        }
    }
</script>
