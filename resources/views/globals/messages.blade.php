<div class="row">
        <div class="col-md-12">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade-in">
                    <a href="#" class="close text-default" data-dismiss="alert" aria-label="close" style="margin-right: 12px;">&times;</a>
                    <ul style="padding-left: 2%">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @elseif (Session::has('success'))
                <div class="alert alert-success alert-dismissible fade-in">
                    <a href="#" class="close text-default" data-dismiss="alert" aria-label="close" style="margin-right: 12px;">&times;</a>
                    <ul style="padding-left: 2%">
						@foreach ((is_array(Session::get('success')) ? Session::get('success') : [Session::get('success')]) as $success)
                        <li>{!! $success !!}</li>
						@endforeach
                    </ul>
                </div>
				@elseif (Session::has('default'))
                <div class="alert alert-success alert-dismissible fade-in">
                    <a href="#" class="close text-default" data-dismiss="alert" aria-label="close" style="margin-right: 12px;">&times;</a>
                    <ul style="padding-left: 2%">
						@foreach ((is_array(Session::get('default')) ? Session::get('default') : [Session::get('default')]) as $default)
                        <li>{!! $default !!}</li>
						@endforeach
                    </ul>
                </div>
            @elseif (Session::has('warning'))
                <div class="alert alert-warning alert-dismissible fade-in">
                    <a href="#" class="close text-default" data-dismiss="alert" aria-label="close" style="margin-right: 12px;">&times;</a>
                    <ul style="padding-left: 2%">
						@foreach ((is_array(Session::get('warning')) ? Session::get('warning') : [Session::get('warning')]) as $warning)
                        <li>{!! $warning !!}</li>
						@endforeach
                    </ul>
                </div>
			@elseif (Session::has('info'))
                <div class="alert alert-info alert-dismissible fade-in">
                    <a href="#" class="close text-default" data-dismiss="alert" aria-label="close" style="margin-right: 12px;">&times;</a>
                    <ul style="padding-left: 2%">
                        @foreach ((is_array(Session::get('info')) ? Session::get('info') : [Session::get('info')]) as $info)
                        <li>{!! $info !!}</li>
						@endforeach
                    </ul>
                </div>
			@elseif (Session::has('error'))
                <div class="alert alert-danger alert-dismissible fade-in">
                    <a href="#" class="close text-default" data-dismiss="alert" aria-label="close" style="margin-right: 12px;">&times;</a>
                    <ul style="padding-left: 2%">
                        @foreach ((is_array(Session::get('error')) ? Session::get('error') : [Session::get('error')]) as $error)
                        <li>{!! $error !!}</li>
						@endforeach
                    </ul>
                </div>
            @endif
        </div>
</div>

@push('scripts')
    <script>

    </script>
@endpush