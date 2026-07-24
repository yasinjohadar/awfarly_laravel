<div class="d-flex justify-content-around">
    <a @cannot('admins.roles.edit') disabled @endcannot href="{{route('admin.roles.edit', $id)}}"
            class="btn btn-secondary">
        <i class="icon-pencil7"></i>
    </a>
</div>
