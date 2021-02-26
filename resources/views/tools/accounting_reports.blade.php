@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')

<div class="preloader"></div>

<div class="wrapper">

	@include('shared.sidenav')
	
	<div id="content">
		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
		    <div class="container-full mt50 accounting_reports">
		        <div class="row">
		            <div class="col-sm-12">
		                <div class="list-group">
		                    <a target="_blank" href="http://powerbi.chasedatacorp.com/PowerBiEmbedded/Home/EmbedReport?workspaceid=5072828d-6001-4717-91e2-d154cb48159d&reportid=991adf03-e2a7-4167-9bb9-3ffffb6a240e" class="list-group-item list-group-item-action d-flex justify-content-between"><span><i class="fas fa-external-link-alt"></i> 07 Admin Report</span> <span class="ip">34.67.55.164</span></a>
		                    <a target="_blank" href="http://powerbi.chasedatacorp.com/PowerBiEmbedded/Home/EmbedReport?workspaceid=67bd9782-576b-4832-8c65-188728de013e&reportid=07c5af8d-091d-42a3-93f9-32571a847b3e" class="list-group-item list-group-item-action d-flex justify-content-between"><span><i class="fas fa-external-link-alt"></i> 08 Admin Report</span> <span class="ip">63.251.97.13</span></a>
		                    <a target="_blank" href="http://powerbi.chasedatacorp.com/PowerBiEmbedded/Home/EmbedReport?workspaceid=f129c787-ed21-48bb-af5c-fb699df4c396&reportid=975ecdc3-fac3-4f3b-8345-350e74757692" class="list-group-item list-group-item-action d-flex justify-content-between"><span><i class="fas fa-external-link-alt"></i> 12 Admin Report</span> <span class="ip">107.6.88.134</span></a>
		                    <a target="_blank" href="http://powerbi.chasedatacorp.com/PowerBiEmbedded/Home/EmbedReport?workspaceid=f9b26544-cab3-4787-aea9-21c0b4438cea&reportid=36f39a9d-8972-48d2-9db6-2f272094e4e2" class="list-group-item list-group-item-action d-flex justify-content-between"><span><i class="fas fa-external-link-alt"></i> 23 Admin Report</span> <span class="ip">107.6.88.136</span></a>
		                    <a target="_blank" href="http://powerbi.chasedatacorp.com/PowerBiEmbedded/Home/EmbedReport?workspaceid=dbe82ced-7538-48a7-af5a-50593bef36c2&reportid=df267712-7573-412c-b15b-08804a1fd1a3" class="list-group-item list-group-item-action d-flex justify-content-between"><span><i class="fas fa-external-link-alt"></i> 24 Admin Report</span> <span class="ip">35.202.216.102</span></a>
		                    <a target="_blank" href="http://powerbi.chasedatacorp.com/PowerBiEmbedded/Home/EmbedReport?workspaceid=1c70fb49-031c-4e26-abed-37230f47bc5d&reportid=bb000b54-4c71-4926-9049-7da9250e71da" class="list-group-item list-group-item-action d-flex justify-content-between"><span><i class="fas fa-external-link-alt"></i> 25 Admin Report</span> <span class="ip">64.7.218.253</span></a>
		                    <a target="_blank" href="http://powerbi.chasedatacorp.com/PowerBiEmbedded/Home/EmbedReport?workspaceid=51551cfd-9ab5-4998-ab6f-c3ad5fc51a6d&reportid=9f03fb38-4c7d-42cf-b6fc-aa59d0350cd5" class="list-group-item list-group-item-action d-flex justify-content-between"><span><i class="fas fa-external-link-alt"></i> 26 Admin Report</span> <span class="ip">35.224.21.108</span></a>
		                </div>
		            </div>
		        </div>
		    </div>
		</div>
	</div>

	@include('shared.notifications_bar')
</div>

@include('shared.reportmodal')

@endsection