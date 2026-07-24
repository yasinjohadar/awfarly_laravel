@props(['type'=>''])
<div class="mb-4">
    @if($type === 'success')
        <x-notifications.success/>
    @elseif($type === 'error')
        <x-notifications.errors/>
    @endif

</div>
