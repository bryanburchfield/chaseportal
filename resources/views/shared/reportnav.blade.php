<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
       <div class="col-xs-3 col-sm-4 brand">
           <button type="button" id="sidebarCollapse" class="btn">
               <i class="fas fa-align-left"></i>
           </button>
           
           <img src="/img/chase_text_logo.png" alt="" class="img-responsive text_logo">
       </div>

        <div class="filters col-xs-9 col-sm-8">
            <div class="input-group">

                <div class="input-group-btn">

                    {{-- <a href="{{ route('logout') }}" class="logout btn btn-primary btn-sm" onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">Log Out</a> --}}

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <div class="btn-group">
                        <button type="button" onclick="window.location.href = {{ route('logout') }};" class="btn logout_btn"><span>Log Out</span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>