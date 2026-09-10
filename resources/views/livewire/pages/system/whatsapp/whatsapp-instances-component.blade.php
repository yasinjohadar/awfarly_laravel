<div>
    <div class="card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between" style="gap: .5rem">
            <h5 class="card-title mb-0">{{__('pages/system/whatsapp.content.instances.title', ['count' => count($instances)])}}</h5>
            <button type="button" wire:click="syncStatus" wire:loading.attr="disabled" wire:target="syncStatus" class="btn btn-sm btn-outline-success">
                <i class="icon-reload-alt mr-1"></i> {{__('pages/system/whatsapp.content.instances.sync')}}
            </button>
        </div>
        <div class="card-body">
            @if($rotationPoolCount > 0)
                <div class="alert alert-info py-2 small mb-3">
                    <i class="icon-shuffle mr-1"></i> {{__('pages/system/whatsapp.content.instances.rotation_active', ['count' => $rotationPoolCount])}}
                </div>
            @endif

            @if($connectedCount > $rotationPoolCount)
                <div class="alert alert-warning py-2 small mb-3">
                    {{__('pages/system/whatsapp.content.instances.rotation_gap', ['count' => $connectedCount - $rotationPoolCount])}}
                </div>
            @endif

            {{-- Add instance --}}
            <form wire:submit.prevent="addInstance" class="form-row align-items-end mb-4">
                <div class="col-md-7 form-group mb-md-0">
                    <label>{{__('pages/system/whatsapp.content.instances.new_name')}}</label>
                    <input type="text" wire:model.defer="new_instance_name"
                           class="form-control @error('new_instance_name') is-invalid @enderror"
                           placeholder="whatsapp 1">
                    @error('new_instance_name')
                        <span class="invalid-feedback" role="alert"><strong>{{$message}}</strong></span>
                    @enderror
                </div>
                <div class="col-md-3 form-group mb-md-0">
                    <div class="form-check mt-4">
                        <input type="checkbox" wire:model.defer="new_set_as_default" class="form-check-input" id="new_set_as_default">
                        <label class="form-check-label" for="new_set_as_default">{{__('pages/system/whatsapp.content.instances.set_as_default_checkbox')}}</label>
                    </div>
                </div>
                <div class="col-md-2 form-group mb-md-0">
                    <button type="submit" class="btn btn-success btn-block" wire:loading.attr="disabled" wire:target="addInstance">
                        <i class="icon-plus3 mr-1"></i> {{__('pages/system/whatsapp.content.instances.add')}}
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr>
                        <th>{{__('pages/system/whatsapp.content.instances.table.name')}}</th>
                        <th>{{__('pages/system/whatsapp.content.instances.table.credentials')}}</th>
                        <th>{{__('pages/system/whatsapp.content.instances.table.status')}}</th>
                        <th>{{__('pages/system/whatsapp.content.instances.table.number')}}</th>
                        <th>{{__('pages/system/whatsapp.content.instances.table.rotation')}}</th>
                        <th class="text-right">{{__('pages/system/whatsapp.content.instances.table.actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($instances as $instance)
                        <tr class="{{ $instance->is_default ? 'table-success' : '' }}">
                            <td>
                                <span class="font-weight-semibold">{{ $instance->instance_name }}</span>
                                @if($instance->is_default)
                                    <span class="badge badge-success ml-1">{{__('pages/system/whatsapp.content.instances.table.default_badge')}}</span>
                                @endif
                                @if($instance->label)
                                    <small class="d-block text-muted">{{ $instance->label }}</small>
                                @endif
                            </td>
                            <td class="small">
                                @if($instance->hasCustomCredentials())
                                    <span class="text-info">{{__('pages/system/whatsapp.content.instances.table.credentials_private')}}</span>
                                @else
                                    <span class="text-muted">{{__('pages/system/whatsapp.content.instances.table.credentials_global')}}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $tone = match ($instance->connection_status) {
                                        'open' => 'success',
                                        'connecting' => 'info',
                                        'not_found' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge badge-{{ $tone }}">
                                    {{ $instance->connection_status === 'not_found' ? __('pages/system/whatsapp.content.instances.table.status_not_found') : $instance->connection_status }}
                                </span>
                            </td>
                            <td>{{ $instance->phone_number ?? '—' }}</td>
                            <td>
                                @if($instance->isConnected())
                                    <button type="button" wire:click="toggleRotation('{{ $instance->instance_name }}')"
                                            class="btn btn-sm {{ $instance->rotation_enabled ? 'btn-success' : 'btn-outline-secondary' }}">
                                        <i class="icon-shuffle"></i>
                                    </button>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">
                                @if(!$instance->is_default)
                                    <button type="button" wire:click="setDefault('{{ $instance->instance_name }}')"
                                            title="{{__('pages/system/whatsapp.content.instances.table.set_default')}}"
                                            class="btn btn-sm btn-outline-success">
                                        <i class="icon-star-full3"></i>
                                    </button>
                                @endif
                                <button type="button" wire:click="showConnect('{{ $instance->instance_name }}')"
                                        title="QR" class="btn btn-sm btn-outline-primary">
                                    <i class="icon-qrcode"></i>
                                </button>
                                <button type="button" wire:click="delete('{{ $instance->instance_name }}')"
                                        onclick="return confirm('{{__('pages/system/whatsapp.content.instances.table.delete_confirm')}}')"
                                        title="{{__('pages/system/whatsapp.content.instances.table.delete')}}"
                                        class="btn btn-sm btn-outline-danger">
                                    <i class="icon-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                {{__('pages/system/whatsapp.content.instances.empty')}}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- QR / connect modal --}}
    @if($showConnectModal)
        <div class="modal fade show" style="display:block" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $connecting_instance_name }}</h5>
                        <button type="button" class="close" wire:click="closeConnectModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body text-center">
                        <p>
                            {{__('pages/system/whatsapp.content.instances.connect.status')}}
                            <span class="badge badge-{{ $connection_status === 'open' ? 'success' : 'secondary' }}">
                                {{ $connection_status ?? '—' }}
                            </span>
                        </p>

                        <div class="mb-3 p-3 bg-light rounded d-inline-block">
                            @if($qr_code)
                                <img src="{{ str_starts_with($qr_code, 'data:') ? $qr_code : 'data:image/png;base64,'.$qr_code }}"
                                     alt="QR" style="max-width:260px" class="rounded">
                            @else
                                <div class="text-muted py-5 px-4">
                                    <i class="icon-qrcode2" style="font-size:48px"></i>
                                    <div class="mt-2">{{__('pages/system/whatsapp.content.instances.connect.no_qr_yet')}}</div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-center" style="gap: .5rem">
                            <button type="button" wire:click="fetchQr" wire:loading.attr="disabled" wire:target="fetchQr" class="btn btn-success">
                                <i class="icon-reload-alt mr-1"></i> {{__('pages/system/whatsapp.content.instances.connect.fetch_qr')}}
                            </button>
                            <button type="button" wire:click="checkStatus" wire:loading.attr="disabled" wire:target="checkStatus" class="btn btn-outline-success">
                                <i class="icon-link mr-1"></i> {{__('pages/system/whatsapp.content.instances.connect.check_status')}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
