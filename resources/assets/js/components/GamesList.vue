<template>
    <div>
        <b-tabs>
            <div v-for="(leagueGames, leagueName) in gamesList" :key="leagueName">
                <b-tab :title="leagueName.toUpperCase()" class="rounded">
                     <!--Games list-->
                    <div v-for="game of leagueGames" class="mt-2 clickable shadow-sm rounded" v-on:click="goToGame(game.urlSegment)">
                        <b-card footer-class="pt-1 pb-1 text-secondary">
                            <b-row>
                                <!-- Team Names -->
                                <b-col cols="6">
                                    <img :src="game.awayTeam.thumbUrl" class="avatar">&nbsp;<span :class="game.awayTeam.isWinner ? 'font-weight-bold' : ''">{{ game.awayTeam.name }}</span> <span v-if="game.awayTeam.isWinner"><i class="fas fa-caret-left"></i></span><br>
                                    <img :src="game.homeTeam.thumbUrl" class="avatar">&nbsp;<span :class="game.homeTeam.isWinner ? 'font-weight-bold' : ''">{{ game.homeTeam.name }}</span> <span v-if="game.homeTeam.isWinner"><i class="fas fa-caret-left"></i></span>
                                </b-col>

                                <!-- Scores -->
                                <b-col v-if="game.status == 'in progress' || game.status == 'ended'">
                                    <!-- In progress or ended games-->
                                    <span :class="game.awayTeam.isWinner ? 'font-weight-bold' : ''">{{ game.awayTeam.score }}</span><br>
                                    <span :class="game.homeTeam.isWinner ? 'font-weight-bold' : ''">{{ game.homeTeam.score }}</span>
                                </b-col>

                                <!-- Time / Status -->
                                <b-col class="text-center">
                                    <span v-if="game.status == 'upcoming'">{{ game.startDate }}<br>{{ game.startTime }}</span>
                                    <span v-if="game.endedAt"><strong>Final</strong></span>
                                    <span v-if="game.status == 'in progress' && game.period">{{ game.period }}</span>
                                    <span v-if="game.status == 'postponed'">Postponed</span>
                                </b-col>
                            </b-row>

                            <template slot="footer" v-if="game.bets.length || game.highlightsCount">
                                <b-row>
                                    <b-col>
                                        <i class="fas fa-money-bill-alt"></i>&nbsp;${{ game.betAmount }} ({{ game.bets.length }})
                                    </b-col>
                                    <b-col>
                                        <i class="fas fa-video"></i>&nbsp;{{ game.highlightsCount }}
                                    </b-col>
                                </b-row>
                            </template>
                        </b-card>
                    </div>

                </b-tab>
            </div>
        </b-tabs>
    </div>
</template>

<script type="text/babel">
    export default {
        props: [
            'gamesByLeague',
        ],
        data: function () {
            return {
                gamesList: this.gamesByLeague
            }
        },
        watch: {
            gamesByLeague: function(games) {
                var self = this;
                self.gamesList = games;
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
        // },
        // mounted: function () {
        //     setInterval(function () {
        //         this.refreshGames(self.date);
        //     }.bind(this), 5 * 60000); // every 5 minutes update the scores
        },
        computed: {
            leagueTitles: function() {
                var self = this;
                console.log(this.gamesList)
            }
        }
    }
</script>
