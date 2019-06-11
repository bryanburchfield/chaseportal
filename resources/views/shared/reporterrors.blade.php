@if($errors->isNotEmpty())
<div class="alert alert-danger report_errors">
    @foreach($errors->all() as $error)
        {{$error}} <br>
    @endforeach
</div>
@endif
