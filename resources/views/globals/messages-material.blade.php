@if ($errors->any())
    <div class="alert alert-danger">
        <a href="#" class="close text-default" data-dismiss="alert" aria-label="close">&times;</a>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@elseif (Session::has('success'))
    <div class="alert alert-success">
        <a href="#" class="close text-default" data-dismiss="alert" aria-label="close">&times;</a>
        <ul>
            @foreach ((is_array(Session::get('success')) ? Session::get('success') : [Session::get('success')]) as $success)
                <li>{!! $success !!}</li>
            @endforeach
        </ul>
    </div>
@elseif (Session::has('warning'))
    <div class="alert alert-warning">
        <a href="#" class="close text-default" data-dismiss="alert" aria-label="close">&times;</a>
        <ul>
            @foreach ((is_array(Session::get('warning')) ? Session::get('warning') : [Session::get('warning')]) as $warning)
                <li>{!! $warning !!}</li>
            @endforeach
        </ul>
    </div>
@elseif (Session::has('info'))
    <div class="alert alert-info alert-dismissible fade-in">
        <a href="#" class="close text-default" data-dismiss="alert" aria-label="close" style="margin-right: 12px;">&times;</a>
        <ul>
            @foreach ((is_array(Session::get('info')) ? Session::get('info') : [Session::get('info')]) as $info)
                <li>{!! $info !!}</li>
            @endforeach
        </ul>
    </div>
@elseif (Session::has('error'))
    <div class="alert alert-danger alert-dismissible fade-in">
        <a href="#" class="close text-default" data-dismiss="alert" aria-label="close" style="margin-right: 12px;">&times;</a>
        <ul>
            @foreach ((is_array(Session::get('error')) ? Session::get('error') : [Session::get('error')]) as $error)
                <li>{!! $error !!}</li>
            @endforeach
        </ul>
    </div>
@endif

@push('scripts')
    <script>
        /*hide alert*/
        $('.alert a[data-dismiss="alert"]').click(function () {
            $($('a[data-dismiss="alert"]').parent()).hide();
        });
    </script>
@endpush