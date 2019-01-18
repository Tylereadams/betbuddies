<template>
    <div v-cloak>

        <!--<div :src="venueThumbUrl" rounded class="bg-gradient-light" fluid-grow />-->

        <div class="jumbotron bg-gradient-light"
        v-bind:style="{ backgroundImage: 'url(' + venueThumbUrl + ')' }"
        style="
        background-size: 100% 190px;
        background-repeat: no-repeat;
        min-height: 190px !important;
        position: relative;
        " v-if="venueThumbUrl">
        </div>

        <div class="container pt-3" v-if="game.homeTeam && game.awayTeam">
            <div class="row text-center game-teams__header">
                <div class="col">
                    <img :src="game.awayTeam.thumbUrl" class="avatar-lg"><br>
                    {{ game.awayTeam.name }}
                    <h3>{{ game.awayTeam.score }}</h3>
                </div>

                <div class="col">
                    <img :src="game.homeTeam.thumbUrl" class="avatar-lg"><br>
                    {{ game.homeTeam.name }}
                    <h3>{{ game.homeTeam.score }}</h3>
                </div>
            </div>

            <div class="row pb-4">
                <div class="col-sm-12">
                    <div class="text-center" v-if="game.status == 'in progress' && game.period">
                        <h4>{{ game.period }} {{ game.league.periodLabel }}</h4>
                    </div>
                    <div class="text-center" v-else-if="game.status == 'postponed'">
                        <strong>Postponed</strong>
                    </div>
                    <div class="text-center" v-else-if="game.endedAt">
                        <strong>Final</strong>
                    </div>
                    <div class="text-center" v-else>
                        <small><span v-if="game.startDate">{{ game.startDate }} </span>{{ game.startTime }}</small><br>
                        <small>{{ game.location }}</small><br>
                        <small>{{ game.broadcast }}</small>
                    </div>
                </div>
            </div>

            <b-row v-if="game.bets.length || highlights.length || game.isBettable">
                <b-col>
                    <b-tabs card>
                        <b-tab title="Bets" class="px-0" v-if="game.bets.length || game.isBettable">
                            <!-- Modal Component -->
                            <b-modal id="modal-center" ref="newBetModal" @ok="handleOk" @cancel="handleCancel" :busy="isLoading" centered title="Create a new bet">

                                <div v-for="error in errors">
                                    <b-alert variant="danger"
                                             dismissible
                                             fade
                                             :show="errors.length"
                                             @dismissed="showDismissibleAlert=false">
                                        {{ error }}<br>
                                    </b-alert>
                                </div>

                                <b-row>
                                    <b-col>
                                        <b-form-group label="Select team">
                                            <b-form-radio-group id="btnradios2"
                                                                buttons
                                                                button-variant="outline-primary"
                                                                v-model="newBet.teamId"
                                                                :options="options" />
                                        </b-form-group>
                                    </b-col>
                                </b-row>

                                <b-row>
                                    <b-col>
                                        <label for="spreadInput">Spread</label>
                                        <b-input-group label="Spread" prepend="+/-">
                                            <b-form-input id="spreadInput" label="Spread:" type="number" min="-100.00" step="0.5" v-model="newBet.spread"></b-form-input>
                                        </b-input-group>
                                    </b-col>
                                    <b-col>
                                        <label for="amountInput">Amount</label>
                                        <b-input-group label="Amount" prepend="$">
                                            <b-form-input id="amountInput" type="number" min="0.00" step="1" v-model="newBet.amount"></b-form-input>
                                        </b-input-group>
                                    </b-col>
                                </b-row>

                            </b-modal>

                            <!-- Bets List-->
                            <bets-list :bets="game.bets" @refreshGame="refreshGame"></bets-list>

                            <div v-if="!game.bets.length && game.isBettable">
                                <div class="jumbotron text-center">
                                    <button type="button" class="btn btn-primary"  v-b-modal.modal-center>Add a Bet</button>
                                </div>
                            </div>
                        </b-tab>

                        <!--Highlights-->
                        <b-tab title="Highlights" v-if="highlights.length">
                            <div v-for="highlight in highlights" class="pb-3">
                                <b-card no-body>
                                    <video :poster="highlight.posterUrl" controls class="embed-responsive embed-responsive-4by3">
                                        <source :src="highlight.url" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>

                                    <b-card-footer>
                                        <span>
                                           <img :src="highlight.logoUrl" class="avatar">
                                        </span>
                                        <span v-for="(player, index) in highlight.players">
                                            <span>{{ player.name}}</span><span v-if="index+1 < highlight.players.length">, </span>
                                        </span>
                                    </b-card-footer>
                                </b-card>
                            </div>
                        </b-tab>
                    </b-tabs>
                </b-col>
            </b-row>

            <nav class="navbar fixed-bottom" v-if="game.isBettable">
                <button type="button" class="btn btn-primary btn-circle btn-lg"  v-b-modal.modal-center><i class="fas fa-plus"></i></button>
            </nav>

        </div>

    </div>
</template>

<script type="text/babel">
    export default {
        props: [
            'urlSegment'
        ],
        data: function () {
            return {
                isLoading: false,
                game: [],
                highlights: [],
                venueThumbUrl: '',
                options: [],
                newBet: {
                    teamId: null,
                    amount: null,
                    spread: null
                },
                errors: []
            }
        },
        methods: {
            refreshGame: function(){
                var self = this;

                self.isLoading = true;

                axios.get('/api/game/' + this.urlSegment).then(response => {
                    self.game = response.data.game;
                    self.highlights = response.data.highlights;
                    self.venueThumbUrl = response.data.venueThumbUrl;
                    self.isLoading = false;
                    self.options = [
                        { text: self.game.homeTeam.name, value: self.game.homeTeam.id },
                        { text: self.game.awayTeam.name, value: self.game.awayTeam.id }
                    ];
                });
            },
            handleOk (evt) {
                // Prevent modal from closing
                evt.preventDefault()
                var self = this;
                self.isLoading = true;

                // Save bet
                axios.post('/api/game/' + this.urlSegment + '/bet', {
                    gameId: self.game.id,
                    teamId: self.newBet.teamId,
                    amount: self.newBet.amount,
                    spread: self.newBet.spread
                }).then(response => {
                    self.errors = response.data.errors;
                    self.isLoading = false;
                    if(!self.errors){
                        this.refreshGame();
                        this.clearNewBet();
                        this.$refs.newBetModal.hide()
                    }
                });
            },
            handleCancel (evt) {
              this.clearNewBet();
              this.$refs.newBetModal.hide()

            },
            clearNewBet: function () {
                var self = this;
                self.newBet = {
                    teamId: null,
                    amount: null,
                    spread: null
                }
            }
        },
        mounted: function () {
            var self = this;

            this.refreshGame();

            setInterval(function () {
                this.refreshGame();
            }.bind(this), .5 * 60000); // every 5 minutes update the scores
        },
        computed: {

        }
    }
</script>