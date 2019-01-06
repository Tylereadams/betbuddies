<template>
    <div>

        <div v-for="bet in bets" class="pb-3">
            <div>
                <div v-for="bet in bets" class="pb-3">
                    <b-card class="shadow-sm rounded"
                            header-tag="header"
                            footer-tag="footer"
                            header-class="p-0">
                        <template slot="header" align-v="center">
                            <div class="text-secondary p-2" style="float:left;">
                                <h4 style="float:left;" class="m-0">
                                    <i class="fas fa-user-circle fa-1x text-muted" v-if="!bet.user.avatarUrl"></i>
                                </h4>&nbsp;
                                <span class="text-secondary">{{ bet.humanDate }}</span>
                            </div>
                            <div class="text-right p-2">
                                <h5 class="m-0">${{ bet.amount }}</h5>
                            </div>
                        </template>

                        <b-row align-v="center">
                            <b-col class="text-center">
                                <img :src="bet.opponentTeam.thumbUrl" class="avatar-lg"><br>
                                <h4>{{ bet.opponentTeam.name }} {{ formatSpread(bet.spread, true) }}</h4>
                                <small class="text-secondary">vs {{ bet.team.name }}</small>
                            </b-col>

                            <!--ACCEPTED BET-->
                            <!--<b-col cols="8">-->
                            <!--<div class="pb-1">-->
                            <!--&lt;!&ndash;Bet User&ndash;&gt;-->
                            <!--<div style="float:left;" class="mr-2 mt-1 align-middle">-->
                            <!--<img :src="bet.user.avatarUrl" class="avatar-md" v-if="bet.user.avatarUrl">-->
                            <!--<i class="fas fa-user-circle fa-2x text-muted" v-if="!bet.user.avatarUrl"></i>-->
                            <!--</div>-->
                            <!--<div>-->
                            <!--<p class="h6 m-0 text-truncate">{{ bet.user.name }}</p>-->
                            <!--{{ bet.team.name }} <small class="text-muted">{{ formatSpread(bet.spread) }}</small>-->
                            <!--</div>-->
                            <!--</div>-->

                            <!--&lt;!&ndash;Opponent&ndash;&gt;-->
                            <!--<div class="pt-1">-->
                            <!--&lt;!&ndash;Avatar&ndash;&gt;-->
                            <!--<div style="float:left;" class="mr-2 mt-1 align-middle">-->
                            <!--<img :src="bet.user.avatarUrl" class="avatar-md" v-if="bet.opponent">-->
                            <!--<button type="button" class="btn btn-outline-success btn-circle btn-lg" v-if="!bet.opponent">?</button>-->
                            <!--</div>-->
                            <!--<div v-if="bet.opponent">-->
                            <!--<p class="h6 m-0 text-truncate">{{ bet.opponent ? bet.opponent.name : 'Thomas Dinkleman' }}</p>-->
                            <!--{{ bet.opponentTeam.name }} <small class="text-muted">{{ formatSpread(bet.spread) }}</small>-->
                            <!--</div>-->
                            <!--<div v-if="!bet.opponent">-->
                            <!--<p class="h6 m-0 text-truncate">-</p>-->
                            <!--<span class="text-muted">{{ bet.opponentTeam.name }} {{ formatSpread(bet.spread) }}</span>-->
                            <!--</div>-->
                            <!--</div>-->
                            <!--</b-col>-->
                            <!--<b-col v-align="center" class="text-center">-->
                            <!--</b-col>-->
                        </b-row>


                        <template slot="footer">
                            <b-row>
                                <b-col>
                                    <!--<b-button href="#" variant="outline-secondary">Share <i class="fas fa-share"></i></b-button>-->
                                </b-col>
                                <b-col cols="4" class="text-right">
                                    <b-button variant="success" v-if="!bet.fromMe && bet.isBettable" v-on:click="acceptBet(bet)">Accept</b-button>
                                    <b-button variant="danger" v-if="bet.fromMe && bet.isBettable">Delete</b-button>
                                </b-col>
                            </b-row>
                        </template>
                    </b-card>
                </div>
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
                console.log('test')
                var self = this;
                // Save bet
                axios.post('/api/bet/' + bet.id + '/accept').then(response => {
                    self.errors = response.data.errors;
                    self.isLoading = false;
                    self.$emit('refreshGames');
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
