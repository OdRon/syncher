<style type="text/css">
    .navbar.navbar-static-top a, .nav.navbar-nav li a {
        color: black;
    }
    @media (min-width: 768px)
    .navbar-nav {
        float: left;
        margin: 0;
        margin-right: 2em;
    }
    .navbar.navbar-static-top a, .nav.navbar-nav li a {
        color: black;
        padding-left: 10px;
        /* padding-right: 10px; */
    }
</style>
<div id="header">
    <div class="">
    </div>
    <div id="logo" class="light-version" style="padding-left: 2px; padding-top: 6px; width: 250px;">
        <span>
            <img src="{{ asset('img/logo.jpg') }}">
        </span>
    </div>
    <nav role="navigation">
        <div class="header-link hide-menu"><i class="fa fa-bars"></i></div>
        <div class="small-logo">
            <span class="text-primary">EID - VL</span>
        </div>
        <form role="search" class="navbar-form-custom" style="width: 400px;">
            <div class="form-group">
                <h4 style="margin-top:1em;">{{ $user->name ?? '' }}</h4>
            </div>
        </form>
        <!-- Mobile view -->
        <div class="mobile-menu">
            <button type="button" class="navbar-toggle mobile-menu-toggle" data-toggle="collapse" data-target="#mobile-collapse">
                <i class="fa fa-chevron-down"></i>
            </button>
            <div class="collapse mobile-navbar" id="mobile-collapse">
                <ul class="nav navbar-nav">
                    <li class="">
                        <a href="{{ url('home') }}">Home</a>
                    </li>
                @if(Auth::user()->user_type_id == 1)
                    <li class="">
                        <a href="{{ url('users') }}">Users</a>
                    </li>
                @endif
                    <li>
                        <a class="" href="{{ url('/logout') }}"
                        onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Mobile view -->
        <!-- Larger screens -->
        <div class="navbar-right">
            <ul class="nav navbar-nav no-borders">
                <li class="">
                    <a href="{{ url('home') }}">Home</a>
                </li>
            @if(Auth::user()->user_type_id == 1)
                <li class="">
                    <a href="{{ url('users') }}">Users</a>
                </li>
            @endif
                <li class="dropdown">
                    <a href="{{ url('/logout') }}"
                        onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();"
                         style="font-size: 25px;"
                    >
                        <i class="pe-7s-upload pe-rotate-90"></i>
                        <!-- <i class="fa fa-sign-out"></i> Log out -->

                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>

                </li>
            </ul>
        </div>
        <!-- Larger screens -->
    </nav>
</div>