<header>
    <nav class="navbar navbar-default navbar-static-top dashboard">
        <div class="container navcontainer">
            <div class="navbar-header">
                <a class="navbar-brand" href="#"><img class="img-responsive logo" src="img/chase_logo_blue.png" alt=""></a>
                <ul class="nav navbar-nav navbar-right">
                    <a href="{{ route('logout') }}" class="logout btn btn-primary btn-sm" onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">Log Out</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </ul>
            </div>
        </div>
    </nav>
</header>
