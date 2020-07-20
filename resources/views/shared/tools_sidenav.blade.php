<nav id="sidebar" class="active">
    <div class="sidebar-header">
        <h3><img class="img-responsive" src="/img/ChaseData-Transparent-Large2.png" alt=""></h3>
        <strong><img src="/img/ChaseData-Transparent-Large2.png" class="img-responsive mb_logo"></strong>
    </div>

    <ul class="list-unstyled components">
        <input type="hidden" class="page_menuitem" value="<?php echo !empty($page['menuitem']) ? $page['menuitem'] : '';?>">
        <li><a class="back_to_sidenav" href="#"><i class="fas fa-arrow-circle-left"></i>{{__('widgets.go_back')}}</a></li>
        @can('accessAdmin')
            <li data-page="dnc_importer" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'dnc_importer' ? 'class="active"' : ''; ?>><a href="{{url('/tools/dnc_importer')}}"><i class="fas fa-user-check"></i>DNC Importer</a></li>
        @endcan

        @can('accessSuperAdmin')
            <li data-page="webhook_generator" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'webhook_generator' ? 'class="active"' : ''; ?>><a href="{{ action('AdminController@webhookGenerator') }}"><i class="fas fa-link"></i> Webhook Generator</a></li>
            <li data-page="cdr_lookup" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'cdr_lookup' ? 'class="active"' : ''; ?>><a href="{{ action('AdminController@cdrLookup') }}"><i class="fas fa-search"></i> {{__('sidenav.cdr_lookup')}}</a></li>
        @endcan
    </ul>
</nav>
