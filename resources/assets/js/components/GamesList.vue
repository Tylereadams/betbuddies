<template>
    <div>
        <b-tabs>
            <div v-for="(leagueGames, key) in gamesList">
                <b-tab>
                    <template slot="title">
                        <b-nav-item>
                            {{ key.toUpperCase() }}
                        </b-nav-item>
                    </template>

                    <template>
                        <b-table hover :items="leagueGames" :fields="fields" thead-class="d-none border-0">

                                <!-- Team Names -->
                                <template slot="teams" slot-scope="data">
                                    <div @click.stop="data.toggleDetails">
                                        <img :src="data.item.awayTeam.thumbUrl" class="avatar">&nbsp;<span :class="data.item.awayTeam.isWinner ? 'font-weight-bold' : ''">{{ data.item.awayTeam.name }}</span> <span v-if="data.item.awayTeam.isWinner"><i class="fas fa-caret-left"></i></span><span v-if="data.item.awayTeam.betCount">({{ data.item.awayTeam.betCount }})</span><br>
                                        <img :src="data.item.homeTeam.thumbUrl" class="avatar">&nbsp;<span :class="data.item.homeTeam.isWinner ? 'font-weight-bold' : ''">{{ data.item.homeTeam.name }}</span> <span v-if="data.item.homeTeam.isWinner"><i class="fas fa-caret-left"></i></span><span v-if="data.item.homeTeam.betCount"> ({{ data.item.homeTeam.betCount }})</span>
                                    </div>
                                </template>

                                <!-- Scores -->
                                <template slot="score" slot-scope="data">
                                    <div @click.stop="data.toggleDetails">
                                        <div v-if="data.item.status == 'in progress' || data.item.status == 'ended'">
                                            <!-- In progress games-->
                                            <span :class="data.item.awayTeam.isWinner ? 'font-weight-bold' : ''">{{ data.item.awayTeam.score }}</span><br>
                                            <span :class="data.item.homeTeam.isWinner ? 'font-weight-bold' : ''">{{ data.item.homeTeam.score }}</span>
                                        </div>
                                    </div>
                                </template>

                                <!-- Time / Status -->
                                <template slot="status" slot-scope="data">
                                    <div @click.stop="data.toggleDetails">
                                        <span v-if="data.item.status == 'upcoming'">{{ data.item.startTime }}</span>
                                        <span v-if="data.item.endedAt"><strong>Final</strong></span>
                                        <span v-if="data.item.status == 'in progress' && data.item.period">{{ data.item.period }}</span>
                                        <span v-if="data.item.status == 'postponed'">Postponed</span>
                                    </div>
                                </template>

                                <template slot="row-details" slot-scope="data">
                                    <b-card class="border-0">
                                        <b-row class="mb-2 text-center" align-v="center">
                                            <b-col><b><i class="fas fa-file-invoice-dollar text-secondary"></i> {{ data.item.bets.length }}</b></b-col>
                                            <b-col><b><i class="fas fa-video text-secondary"></i> {{ data.item.highlightsCount }}</b></b-col>
                                            <b-col><b-button href="#" class="text-white" variant="primary">View game <i class="fas fa-caret-right"></i></b-button></b-col>
                                        </b-row>

                                    </b-card>
                                </template>

                        </b-table>
                    </template>

                    <!--<div class='row pt-3' v-on:click="goToGame(game.urlSegment)" v-for="(game, key) in leagueGames">-->

                    <!--</div>-->
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
                gamesList: this.gamesByLeague,
                fields: [
                    'teams',
                    'score',
                    'status'
                ]
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
