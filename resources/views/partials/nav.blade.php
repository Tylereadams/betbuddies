<nav class="navbar navbar-light bg-light mb-2">

    {{--<!-- Collapse button -->--}}
    {{--<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navMenu" aria-controls="navMenu"--}}
            {{--aria-expanded="false" aria-label=""><span class="dark-blue-text"><i class="fa fa-bars fa-1x"></i></span></button>--}}
    <div class="dropdown">
        <button class="btn btn-secondary" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-bars fa-1x"></i>
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
            <button class="dropdown-item" type="button">Action</button>
            <button class="dropdown-item" type="button">Another action</button>
            <button class="dropdown-item" type="button">Something else here</button>
        </div>
    </div>

    <!-- Authentication Links -->
    @guest
        <ul class="nav">
            <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
        </ul>
    @else
        <a class="navbar-brand" href="{{ url('/user') }}">
            <i class="far fa-user text-secondary"></i>
        </a>

    @endguest
</nav>


{{--<!-- Collapsible content -->--}}
{{--<div class="collapse navbar-collapse" id="navMenu">--}}

    {{--<div class="dropdown-menu navMenu" aria-labelledby="navMenu">--}}
        {{--<a class="navbar-brand dropdown-item" href="{{ url('/home') }}">--}}
            {{--<i class="fas fa-home text-secondary"></i>--}}
        {{--</a>--}}
        {{--<a class="navbar-brand dropdown-item" href="{{ url('/games') }}">--}}
            {{--<i class="fas fa-calendar-alt text-secondary"></i>--}}
        {{--</a>--}}
        {{--<a class="nav-link" href="{{ route('logout') }}">Logout</a>--}}
    {{--</div>--}}

{{--</div>--}}

{{--<!-- Collapsible content -->--}}