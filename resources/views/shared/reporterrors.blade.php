<div class='reporterrors'>
    @if($errors->isNotEmpty())
    <div class="alert alert-danger report_errors">
        @foreach($errors->all() as $error)
            {{$error}}
        @endforeach
    </div>
    @endif
</div>
