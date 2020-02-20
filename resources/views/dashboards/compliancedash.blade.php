<div class="container-full mt20">
    <div class="row">
        <div class="col-sm-12">
            <div class="filter_time_camp_dets">
                <p>
                    <span class="selected_datetime"></span> |
                    <span class="selected_campaign"></span>
                </p>
            </div>
        </div>
    </div>
    @if (request()->has('settings'))
        @include('shared.compliance_settings')
    @else
        @include('shared.compliancedash')
    @endif
</div>
