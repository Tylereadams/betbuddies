<template>
    <b-container class="p-2" v-cloak>

        <h2>Games</h2>
        <hr>
        <games-list v-bind:games-by-league="gamesList" :class="isLoading ? 'fade' : 'show'"></games-list>

    </b-container>
</template>

<script type="text/babel">
    export default {
        data: function () {
            return {
                gamesList: [],
                isLoading: false,
                date: new Date(),
            }
        },
        methods: {
            refreshGames: function(date){
                var self = this;

                self.isLoading = true;

                axios.get('/api/games/' + this.getFormattedDate()).then(response => {
                    self.gamesList = response.data.gamesByLeague;
                    self.isLoading = false;
                });
            },
            goToGame: function(urlSegment){
                window.location.href = '/game/' + urlSegment;
            },
            changeDate: function(daysToAdd){
                var self = this;

                var newDate = new Date(self.date);

                self.date.setDate(newDate.getDate() + daysToAdd);

                this.refreshGames(this.getFormattedDate());
            },
            getFormattedDate: function(){
                var self = this;

                return self.date.toISOString().split('T')[0];
            }
        },
        mounted: function () {
            var self = this;

            this.refreshGames(self.date);

            setInterval(function () {
                this.refreshGames(self.date);
            }.bind(this), 5 * 60000); // every 5 minutes update the scores
        },
        computed: {
            // a computed getter
            formattedDate: function () {
                var self = this;

                return this.date.toISOString().split('T')[0];
            }
        }
    }
</script>
