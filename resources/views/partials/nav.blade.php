<b-navbar toggleable="md" type="dark" variant="primary">

    <b-navbar-toggle target="nav_collapse"></b-navbar-toggle>

    <b-navbar-brand href="#"><i class="fas fa-users"></i> BetBuddies</b-navbar-brand>

    <b-navbar-nav class="ml-auto">
        @auth
            <b-nav-item href="{{ url('/user') }}">{{ Auth::user()->name }} <i class="far fa-user"></i></b-nav-item>
        @endauth
    </b-navbar-nav>

    <b-collapse is-nav id="nav_collapse">

        <b-navbar-nav>
            <b-nav-item left href="{{ url('/games') }}">
                    <i class="fas fa-calendar-alt"></i> Games
            </b-nav-item>
        </b-navbar-nav>

        <b-navbar-nav class="ml-auto">
            @auth
                <b-nav-item href="{{ url('/logout') }}"><i class="fas fa-sign-out-alt"></i> Logout</b-nav-item>
            @endauth

            @guest
                <b-nav-item href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Login</b-nav-item>
                <b-nav-item href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Sign up</b-nav-item>
            @endguest
        </b-navbar-nav>

    </b-collapse>

</b-navbar>