<template>
    <div>

        <div v-for="bet in bets" class="pb-3">
            <div>
                <b-card class="shadow-sm rounded"
                        header-tag="header"
                        footer-tag="footer"
                        header-class="p-0">
                    <template slot="header" align-v="center">
                        <div class="text-secondary p-2" style="float:left;">
                            <h4 style="float:left;" class="m-0">
                                <i class="fas fa-user-circle fa-1x text-muted" v-if="!bet.user.avatarUrl" v-b-popover.hover="{content:bet.user.name}"></i>
                            </h4>&nbsp;
                            <span class="text-secondary">{{ bet.humanDate }}</span>
                        </div>
                        <div class="text-right p-2">
                            <h5 class="m-0">${{ bet.amount }}</h5>
                        </div>
                    </template>

                    <!--TODO: Fix THE SPREAD FORMATTING-->
                    <b-row align-v="center" class="text-center">
                        <b-col class="text-center" v-if="bet.isAcceptable">
                            <img :src="bet.team.thumbUrl" class="avatar-lg"><br>
                            <h4>{{ bet.team.name }} <span class="font-weight-light">{{ formatSpread(bet.spread, true) }}</span></h4>
                            <small class="text-secondary">vs {{ bet.opponentTeam.name }}</small>
                        </b-col>

                        <b-col v-if="!bet.isAcceptable">
                            <img :src="bet.game.awayTeam.thumbUrl" class="avatar-lg"><br>
                            <h4>{{ bet.game.awayTeam.name }} <span class="font-weight-light">{{ formatSpread(bet.spread, true) }}</span></h4>
                        </b-col>
                        <b-col v-if="!bet.isAcceptable">
                            <img :src="bet.game.homeTeam.thumbUrl" class="avatar-lg"><br>
                            <h4>{{ bet.game.homeTeam.name }} <span class="font-weight-light">{{ formatSpread(bet.spread) }}</span></h4>
                        </b-col>
                    </b-row>

                    <template slot="footer">

                        <!--ACCEPTED BET-->
                        <b-row align-v="center" class="text-center" v-if="!bet.isAcceptable">
                            <b-col>
                                <h6 class="text-secondary m-0"><i class="fas fa-user-circle fa-1x text-muted" v-b-popover.hover="{content:bet.user.name}"></i> {{ bet.opponent.name }}</h6>
                            </b-col>
                            <b-col>
                                <h6 class="text-secondary m-0"><i class="fas fa-user-circle fa-1x text-muted" v-b-popover.hover="{content:bet.user.name}"></i> {{ bet.user.name }}</h6>
                            </b-col>
                        </b-row>

                        <b-row v-if="bet.isAcceptable">
                            <b-col class="text-center">
                                <!--<b-button href="#" variant="outline-secondary">Share <i class="fas fa-share"></i></b-button>-->
                            </b-col>
                            <b-col class="text-center">
                                <b-button variant="success" v-if="!bet.fromMe" v-on:click="acceptBet(bet)">Accept</b-button>
                                <b-button variant="danger" v-if="bet.fromMe" v-on:click="deleteBet(bet)">Delete</b-button>
                            </b-col>
                        </b-row>
                    </template>
                </b-card>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    export default {
        props: [
            'bets'
        ],
        data: function () {
            return {
                isLoading: false
            }
        },
        methods: {
            formatSpread: function(spread, inverse = false) {
                spread = parseInt(spread);
                if(inverse){
                    spread = (spread * -1);
                }
                return spread > 0 ? '+' + spread : spread;
            },
            acceptBet: function(bet) {
                var self = this;
                self.isLoading = true;
                // Save bet
                axios.post('/api/bet/' + bet.id + '/accept').then(response => {
                    self.errors = response.data.errors;
                    self.isLoading = false;
                    self.$emit('refreshGame');
                })
            },
            deleteBet: function(bet) {
                var self = this;
                self.isLoading = true;
                // Save bet
                axios.delete('/api/bet/' + bet.id ).then(response => {
                    self.errors = response.data.errors;
                    self.isLoading = false;
                    self.$emit('refreshGame');
                })
            }
        },
        mounted: function () {
            // setInterval(function () {
            //     this.refreshGames();
            // }.bind(this), 5 * 60000); // every 5 minutes update the scores
        }
    }
</script>
