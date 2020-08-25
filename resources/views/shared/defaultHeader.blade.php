<header>
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container navcontainer">
            <div class="navbar-header home">
                @auth
                    @if(Auth::user()->theme == 'dark')
                        <a class="navbar-brand" href="/" ><img class="img-fluid logo" src="/img/logo-footer.png" alt=""></a>
                    @else
                    	<a class="navbar-brand" href="/" ><img class="img-fluid logo" src="/img/logo_white_bg.jpg" alt=""></a>
                    @endif
                @endauth
                @guest
                    <a class="navbar-brand" href="/" ><img class="img-fluid logo" src="/img/logo_white_bg.jpg" alt=""></a>
                @endguest
            </div>
        </div>
    </nav>
</header>