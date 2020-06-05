<nav id="sidebar" class="active">
    <div class="sidebar-header">
        <h3><img class="img-responsive" src="/img/ChaseData-Transparent-Large2.png" alt=""></h3>
        <strong><img src="/img/ChaseData-Transparent-Large2.png" class="img-responsive mb_logo"></strong>
    </div>

    <ul class="list-unstyled components">
        <input type="hidden" class="page_menuitem" value="<?php echo !empty($page['menuitem']) ? $page['menuitem'] : '';?>">
        <li><a class="back_to_sidenav" href="#"><i class="fas fa-arrow-circle-left"></i>{{__('widgets.go_back')}}</a></li>
        @can('accessAdmin')
            <li data-page="playbook" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'playbook' ? 'class="active"' : ''; ?>><a href="{{action('PlaybookController@index')}}"><i class="fas fa-book"></i>Contacts Playbook</a></li>
            <li data-page="dnc_importer" <?php echo !empty($page['menuitem']) && $page['menuitem']== 'dnc_importer' ? 'class="active"' : ''; ?>><a href="{{url('/tools/dnc_importer')}}"><i class="fas fa-user-check"></i>DNC Importer</a></li>
        @endcan
    </ul>
</nav>
