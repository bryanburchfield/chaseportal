<ul class="nav nav-tabs">
    <li @if ($toolpage == 'rules') class="active" @endif><a href="{{url('/tools/contactflow_builder')}}">Contact Flow Builder</a></li>
    <li @if ($toolpage == 'dnc') class="active" @endif><a href="{{url('/tools/dnc_importer')}}">DNC Importer</a></li>
</ul>
