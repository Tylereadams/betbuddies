<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Bet Buddies') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <meta property="og:title" content="BetBuddies.co" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:image" content="{{ url('/img/wide_open_graph.jpg') }}" />
    <meta property="og:description" content="Custom Bets. Peer to Peer. Totally Free." />
    <meta name="twitter:card" content="summary" />


    @yield('header')

</head>
<body>
    <div id="app">
        @include('partials.nav')
        @include('partials.errors')
        @yield('content')
        @include('partials.footer')
        @yield('modal')
    </div>

    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    {{--<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>--}}
    {{--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>--}}
    {{--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>--}}

    <!-- Autocomplete -->
    {{--<script src="https://cdn.rawgit.com/xcash/bootstrap-autocomplete/v2.0.0/dist/latest/bootstrap-autocomplete.min.js"></script>--}}

    {{-- Custom Scripts --}}
    <script src="{{ asset('js/app.js') }}"></script>

    @yield('scripts')

</body>
</html>