@if($value)
    {{--@include('livewire.datatables.icons.check-circle-fill', ['classIcon' => 'text-success'])--}}
    <div class="badge badge-success">{{__('datatable.yes')}}</div>
@else
    {{--@include('livewire.datatables.icons.x-circle-fill', ['classIcon' => 'text-danger text-lg'])--}}
    <div class="badge badge-danger">{{__('datatable.no')}}</div>
@endif

