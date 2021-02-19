<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
       <div class="col-xs-2 col-sm-6 brand pl0">
           @if(!session()->has('isSso'))
            <button type="button" id="sidebarCollapse" class="btn flt_lft">
                <i class="fas fa-align-left"></i>
            </button>
           @endif

           @if(Auth::user()->theme == 'dark')
               <img src="/img/text_logo_light.png" alt="" class="img-responsive text_logo flt_lft">
           @else
               <img src="/img/chase_text_logo.png" alt="" class="img-responsive text_logo flt_lft">
           @endif
       </div>

        <div class="filters col-xs-10 col-sm-6">
            
            @if(!session()->has('isSso'))
                {!! Form::open(['method'=>'GET', 'action'=>'Auth\LoginController@logout', 'id'=> 'logout-form']) !!}
                    @csrf
                    <div class="btn-group float-right d-flex"">
                    {!! Form::submit(__('general.logout'),['class'=>'btn logout_btn']) !!}
                    </div>
                {!! Form::close() !!}
            @endif

            @if($page['type'] =='dash')
                @include('dashboards.dashnav')
            @elseif($page['type'] == 'kpi_page')
                @include('dashboards.kpinav')
            @elseif($page['type'] == 'report')
                @include('dashboards.reportnav')
            @elseif($page['type'] == 'recipients')
                @include('dashboards.recipientsnav')
            @endif

            @if(Auth::user()->language_displayed)
                <div class="dropdown float-right">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <span><i class="fas fa-globe-americas"></i> Language</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right py-0 stop-propagation">
                        <a class="dropdown-item" href="{{url('lang/en')}}">English</a>
                        <a class="dropdown-item" href="{{url('lang/es')}}"> Español</a>
                    </div>
                </div>
            @endif

             @if(!session()->has('isSso'))
                <li class="notifications btn-group float-right d-flex"">
                    <button type="button" class="btn btn-notifications">
                        <span>
                            <i class="fas fa-bell"></i>
                            @if(Auth::user()->unreadFeatureMessagesCount() != 0)
                                <span class="numb_notifications">{{Auth::user()->unreadFeatureMessagesCount()}}</span>
                            @endif
                        </span>
                    </button>
                </li>
            @endif
        </div>
    </div>
</nav>