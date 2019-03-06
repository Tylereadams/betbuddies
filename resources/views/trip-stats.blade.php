@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="pt-2">Fuel Costs</h2>
        <hr>

        <div class="jumbotron bg-white">
            <div class="row">
                <div class="col text-center text-secondary">
                    <h2><i class="fas fa-car "></i></h2>
                    <h3>{{ count($trips) }} trips</h3>
                </div>
                <div class="col text-center text-secondary">
                    <h2><i class="fas fa-globe-americas"></i></h2>
                    <h3>{{ $totals['miles'] }} miles</h3>
                </div>
                <div class="col text-center text-secondary">
                    <h2><i class="fas fa-money-bill-alt"></i></h2>
                    <h3>${{ money_format('%(#10n', $totals['cost']) }}</h3>
                </div>
            </div>
        </div>

        <table class="table table-hover table-bordered" id="trip-stats">
            <caption>Fuel costs per trip</caption>
            <thead class="thead-light">
            <tr class="clickable">
                <th scope="col">Date</th>
                <th scope="col">Start</th>
                <th scope="col">End</th>
                <th scope="col">Gallons Consumed</th>
                <th scope="col">Miles Traveled</th>
                <th scope="col">MPG</th>
                <th scope="col" title="Source: http://www.mygasfeed.com">Cost</th>
            </tr>
            </thead>
            <tbody>
            @foreach($trips as $trip)
                <tr>
                    <td>{{ $trip['startDate'] }}</td>
                    <td>{{ str_limit($trip['startLocation'], 30, '...') }}</td>
                    <td>{{ str_limit($trip['endLocation'], 30, '...') }}</td>
                    <td>{{ $trip['gallonsConsumed'] }}</td>
                    <td>{{ $trip['milesTraveled'] }}</td>
                    <td>{{ $trip['mpg'] }}</td>
                    <td>${{ money_format('%(#10n', $trip['gasCost']) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot class="thead-light">
            <tr>
                <th colspan="3" class="text-right">Total:</th>
                <th scope="col">{{ $totals['gallons'] }}</th>
                <th scope="col">{{ $totals['miles'] }}</th>
                <th scope="col">{{ $totals['avgMpg'] }}</th>
                <th scope="col">${{ money_format('%(#10n', $totals['cost']) }}</th>
            </tr>
            </tfoot>
        </table>

        <hr>

        <div id="chartContainer" style="height: 300px; width: 100%;"></div>

    </div>
@endsection


@section('scripts')

    <script src="https://code.jquery.com/jquery-3.3.1.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>

    <script>
        $(document).ready(function() {
            $('#trip-stats').DataTable({
                "paging": false,
            });

            Highcharts.chart('chartContainer', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Distance vs Fuel vs Cost'
                },
                subtitle: {
                    text: 'by trip'
                },
                xAxis: {
                    categories: [@foreach($trips as $trip) '{{ $trip['startDate'] }}', @endforeach],
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: ''
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>{point.y:.2f}</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: 'Miles',
                    data: [ @foreach($trips as $trip) {{ $trip['milesTraveled'].", " }} @endforeach],
                }, {
                    name: 'Gallons',
                    data: [ @foreach($trips as $trip) {{ $trip['gallonsConsumed'].", " }} @endforeach]

                }, {
                    name: 'Cost',
                    data: [ @foreach($trips as $trip) {{ money_format('%(#10n', $trip['gasCost']).", " }} @endforeach]
                }]
            });

        });
    </script>

    <style>
        .dataTables_info {
            display:none;
        }
        .dataTables_filter {
            float: right !important;
        }
    </style>
@endsection