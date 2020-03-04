<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
       <div class="col-xs-2 col-sm-6 brand pl0">
           <button type="button" id="sidebarCollapse" class="btn">
               <i class="fas fa-align-left"></i>
           </button>

           @if(Auth::user()->theme == 'dark')
               <img src="/img/text_logo_light.png" alt="" class="img-responsive text_logo">
           @else
               <img src="/img/chase_text_logo.png" alt="" class="img-responsive text_logo">
           @endif
       </div>

        <div class="filters col-xs-10 col-sm-6">

            <div class="input-group">

                <div class="input-group-btn">

                    {!! Form::open(['method'=>'GET', 'action'=>'Auth\LoginController@logout', 'id'=> 'logout-form']) !!}
                        @csrf
                        <div class="btn-group">
                        {!! Form::submit(__('general.logout'),['class'=>'btn logout_btn']) !!}
                        </div>
                    {!! Form::close() !!}

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
                        <li class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <span><i class="fas fa-globe-americas"></i> Language</span>
                            </button>

                            <ul class="dropdown-menu lang_select stop-propagation">
                                <li><a class="dropdown-item" href="{{url('lang/en')}}">English</a></li>
                                <li><a class="dropdown-item" href="{{url('lang/es')}}"> Español</a></li>
                            </ul>
                        </li>
                    @endif

                    <li class="notifications btn-group">
                        <button type="button" class="btn btn-notifications">
                            <span>
                                <i class="fas fa-bell"></i>
                                @if(Auth::user()->unreadFeatureMessagesCount())
                                    <span class="numb_notifications">{{Auth::user()->unreadFeatureMessagesCount()}}</span>
                                @endif
                            </span>
                        </button>
                    </li>
                </div>
            </div>

        </div>

    </div>
</nav>