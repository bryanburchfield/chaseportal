<div id="sidebar_nots" class="">
    <div class="not_header">
    	<div class="not_header_inner">
    		<a href="#" class="close_nots_bar"><i class="fas fa-times-circle"></i></a>
    		<h4>Whats New</h4>
    		<label class="checkbox-inline">
    		    <input type="checkbox" value=""> Notify me about updates
    		</label>
    	</div>
    </div>

    <div class="notifications">
		@foreach(Auth()->User()->getFeatureMessages() as $msg)
			<div class="not {{!$msg->readFeatureMessages->where('user_id',Auth::User()->id)->first() ? 'unread' : ''}}" data-msgid="{{$msg->id}}">
				@if (!$msg->readFeatureMessages->where('user_id',Auth::User()->id)->first())
					<div class="not_read"></div>
				@endif
	    		<p class="not_date">{{Carbon\Carbon::parse($msg->created_at)->format('M j, Y')}}</p>
	    		<h4>{{$msg->title}}</h4>
	    		<p>{{$msg->body}}</p>
	    	</div>
    	@endforeach
    </div>
</div>