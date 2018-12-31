<template>
    <b-container>

        <div v-for="bet in bets" class="pb-2">
            <b-card
                    header-tag="header"
                    footer-tag="footer">
                <h6 slot="header" class="mb-0">
                    <b-row align-v="center">
                        <b-col>
                            <img :src="bet.user.avatarUrl">{{ bet.user.name }} created a bet on the {{ bet.team.name }} {{ formatSpread(bet.spread) }}
                        </b-col>
                    </b-row>
                </h6>

                <b-row align-v="center" class="text-center">
                    <b-col>
                        <img class="avatar-lg" :src="bet.opponentTeam.thumbUrl">
                        <h5>{{ bet.opponentTeam.name }} <small>{{ formatSpread(bet.spread, true) }}</small></h5>
                    </b-col>
                </b-row>

                <template slot="footer">
                    <b-row align-v="center" class="text-center">
                        <b-col>
                            <h5>${{ bet.amount }}</h5>
                        </b-col>
                        <b-col>
                            <b-button href="#" variant="success" v-if="!bet.fromMe">Accept</b-button>
                            <b-button href="#" variant="danger" v-if="bet.fromMe">Delete</b-button>
                        </b-col>
                    </b-row>
                </template>
            </b-card>
        </div>
    </b-container>
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
            // refreshBets: function(){
            //     var self = this;
            //
            //     self.isLoading = true;
            //
            //     axios.get('/api/games/' + self.date).then(response => {
            //         self.betList = response.data.gamesByLeague;
            //         self.isLoading = false;
            //     });
            // }
            formatSpread: function(spread, inverse = false) {
                spread = parseInt(spread);
                if(inverse){
                    spread = (spread * -1);
                }
                return spread > 0 ? '+' + spread : spread;
            }
        },
        mounted: function () {
            // setInterval(function () {
            //     this.refreshGames();
            // }.bind(this), 5 * 60000); // every 5 minutes update the scores
        }
    }
</script>
