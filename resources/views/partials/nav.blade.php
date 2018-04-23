<nav class="navbar navbar-light bg-light mb-2">
    <a class="navbar-brand" href="{{ url('/games') }}">
        <i class="fas fa-calendar-alt text-secondary"></i>
    </a>
    <a class="navbar-brand" href="{{ url('/profile') }}">
        <i class="far fa-user text-secondary"></i>
    </a>
    <!-- Authentication Links -->
    @guest
        <ul class="nav">
            <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
        </ul>
    @else
        <small>Hello, {{ Auth::user()->name }}</small>

        <!-- Collapse button -->
        <button class="navbar-toggler toggler-example" type="button" data-toggle="collapse" data-target="#navbarSupportedContent1" aria-controls="navbarSupportedContent1"
                aria-expanded="false" aria-label="Toggle navigation"><span class="dark-blue-text"><i class="fa fa-bars fa-1x"></i></span></button>

        <!-- Collapsible content -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent1">

            <!-- Links -->
            <ul class="nav justify-content-end">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ route('logout') }}">Logout</a>
                </li>
            </ul>
            <!-- Links -->

        </div>
        <!-- Collapsible content -->

    @endguest
</nav>