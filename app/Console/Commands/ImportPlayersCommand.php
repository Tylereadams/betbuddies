<?php

namespace App\Console\Commands;

use App\Leagues;
use App\Players;
use Illuminate\Console\Command;
use App\Teams;
use Thujohn\Twitter\Facades\Twitter;
use Carbon\Carbon;

class ImportPlayersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'betbuddies:import-players';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports players name, position, twitter and team by loading fantasy pros CSV';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $leagues = Leagues::all();

        foreach($leagues as $league){

            print "Importing ".$league->name." players \n";

            $teams = Teams::where('league_id', $league->id)->get();

            // Get players from downloaded CSV
            $csvFile = 'storage/'.$league->name.'_players.csv';
            $playerData = $this->readCSV($csvFile);

            // Insert players
            foreach($playerData as $key => $player){
                if($key == 0 || !$player[0] || $player[3] == 'DST'){
                    continue;
                }

                $playerData = $this->mapPlayerData($player, $league);

                // Get team id
                $teamId = $teams->where('slug', $league->name.'-'.strtolower($playerData['teamName']))->pluck('id');

                // Get player name
                $name = explode(' ', $playerData['name']);

                Players::updateOrCreate([
                    'first_name' => isset($name[0]) ? $name[0] : NULL,
                    'last_name' => isset($name[1]) ? $name[1] : NULL,
                ], [
                    'team_id' => $teamId->first(),
                    'position' => $playerData['position'],
                ]);
            }

            // Pull players
            $players = Players::whereNotNull('team_id')->whereNull('twitter')->get();

            // Lookup their twitter handle via Twitter API
            foreach($players as $updatedPlayer){
                $results = Twitter::getUsersSearch([
                    'q' => $updatedPlayer->first_name.' '.$updatedPlayer->last_name,
                    'count' => 3
                ]);

                if(empty($results) || !$results[0]->verified){
                    continue;
                }

                $updatedPlayer->twitter = $results[0]->screen_name;
                $updatedPlayer->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                $updatedPlayer->save();
            }
        }
    }

    /**
     * Reads the CSV of player data
     * @param $csvFile
     * @return array
     */
    private function readCSV($csvFile){
        $file_handle = fopen($csvFile, 'r');
        while (!feof($file_handle) ) {
            $line_of_text[] = fgetcsv($file_handle, 1024);
        }
        fclose($file_handle);
        return $line_of_text;
    }

    /**
     * Maps the Fantasy pros data to the DB
     * @param $player
     * @param Leagues $league
     * @return array
     */
    private function mapPlayerData($player, Leagues $league)
    {
        switch($league->name) {
            case 'nba':
                $playerData = [
                    'name' => $player[0],
                    'teamName' => $player[1],
                    'position' => $player[2],
                ];
                break;
            case 'nfl':
                $playerData = [
                    'name' => $player[1],
                    'teamName' => $player[2],
                    'position' => $player[3],
                ];
                break;
            case 'nhl':
                $playerData = [
                    'name' => $player[1],
                    'teamName' => $player[2],
                    'position' => $player[3],
                ];
                break;
            case 'mlb':
                $playerData = [
                    'name' => $player[1],
                    'teamName' => $player[2],
                    'position' => $player[3],
                ];
                break;
        }

        return $playerData;

    }

}
