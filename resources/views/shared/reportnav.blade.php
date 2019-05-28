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

                    {!! Form::open(['method'=>'GET', 'action'=>'Auth\LoginController@logout', 'id'=> 'logout-form']) !!}
                        @csrf
                        <div class="btn-group">
                        {!! Form::submit('Log Out',['class'=>'btn logout_btn']) !!}
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</nav>