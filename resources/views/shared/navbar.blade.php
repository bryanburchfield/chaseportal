<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
       <div class="col-xs-5 col-sm-6 brand pl0">
           <button type="button" id="sidebarCollapse" class="btn">
               <i class="fas fa-align-left"></i>
           </button>
           
           <img src="/img/chase_text_logo.png" alt="" class="img-responsive text_logo">
       </div>

        <div class="filters col-xs-7 col-sm-6">
            <div class="input-group">

                <div class="input-group-btn">
                    
                    {!! Form::open(['method'=>'GET', 'action'=>'Auth\LoginController@logout', 'id'=> 'logout-form']) !!}
                        @csrf
                        <div class="btn-group">
                        {!! Form::submit('Log Out',['class'=>'btn logout_btn']) !!}
                        </div>
                    {!! Form::close() !!}



                    @if($page['type'] =='dash')
                        @include('dashboards.dashnav')
                    @elseif($page['type'] == 'kpi_page')
                        @include('dashboards.kpinav')
                    @elseif($page['type'] == 'report')
                        @include('dashboards.reportnav')
                    @endif
                </div>
            </div>
        </div>

    </div>
</nav>