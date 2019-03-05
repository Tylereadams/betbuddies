<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class SamsaraController extends Controller
{
    /**
     * Returns a view with the cost per trip and cost totals of all trips retrieved from Samsara API
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getTripData()
    {
        // Setup data to pass back to view
        $data['totals'] = [
                'gallons' => 0,
                'miles' => 0,
                'cost' => 0,
                'avgMpg' => 0
        ];

        $beginTime = (strtotime('-90 days') * 1000);
        $endTime = (strtotime('now') * 1000);

        // Get data about trips from Samsara API
        $response = $this->getTripsDetails($beginTime, $endTime);

        // Loop through
        foreach($response->trips as $trip) {
            $milesTraveled = $this->convertMetersToMiles($trip->distanceMeters);
            $gallonsConsumed = $this->convertMilliletersToGallons($trip->fuelConsumedMl);

            // Get price of gas from nearest station to end of trip location
            $localGasPrice = $this->getLocalGasPrice($trip->endCoordinates->latitude, $trip->endCoordinates->longitude);

            // Setup Data to pass to view
            $data['trips'][] = [
                'startDate' => date("M j, Y g:ia", ($trip->startMs / 1000)),
                'startLocation' => $trip->startLocation,
                'endLocation' => $trip->endLocation,
                'gallonsConsumed' => round($gallonsConsumed, 2),
                'milesTraveled' => round($milesTraveled, 2),
                'gasCost' => round($gallonsConsumed * $localGasPrice, 2),
                'mpg' => $gallonsConsumed ? round($milesTraveled / $gallonsConsumed, 2) : 0,
                'currentTrip' => !$trip->endLocation ? true : false
            ];

            // Add up totals
            $data['totals']['gallons'] += round($gallonsConsumed, 2);
            $data['totals']['miles'] += round($milesTraveled, 2);
            $data['totals']['cost'] += round($gallonsConsumed * $localGasPrice, 2);
        }

        // Calculate average miles per gallon
        $data['totals']['avgMpg'] = round($data['totals']['miles'] / $data['totals']['gallons'], 2);

        return view('trip-stats', $data);
    }

    /**
     * Get the regular gas price from the nearest gas station given a pair of coordinates.
     * @param $latitude
     * @param $longitude
     * @return int
     */
    private function getLocalGasPrice($latitude, $longitude)
    {
        // Search radius
        $milesAway = 5;

        // Cache the response from gas buddy for 24 hours,
        // they aren't changing frequently enough to get new data on each page load
        $response = Cache::remember('gas-prices-'.$milesAway.'-'.$latitude.'-'.$longitude, 60 * 24, function () use ($milesAway, $latitude, $longitude) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://api.mygasfeed.com/stations/radius/" . $latitude . "/" . $longitude . "/" . $milesAway . "/reg/distance/725assm5ub.json",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET"
            ));

            return json_decode(curl_exec($curl));
        });

        return (int) reset($response->stations)->reg_price;
    }

    /**
     * Returns trip details from Samsara API given a start and end time (milliseconds)
     * @param $beginTime
     * @param $endTime
     * @return mixed
     */
    private function getTripsDetails($beginTime, $endTime)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.samsara.com/v1/fleet/trips?access_token=V5ayUslKsKrrhRabQkaV3xmPCf9NPS&vehicleId=212014918430882&startMs=".$beginTime."&endMs=".$endTime."&groupId=26340",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
        ));
        $response = json_decode(curl_exec($curl));

        curl_close($curl);

        return $response;
    }

    /**
     * Convert meters to miles
     * @param $meters
     * @return float
     */
    private function convertMetersToMiles($meters)
    {
        return $meters / 1609.344;
    }

    /**
     * Convert millileters to gallons
     * @param $millileters
     * @return float
     */
    private function convertMilliletersToGallons($millileters)
    {
        return $millileters * 0.000264172;
    }
}