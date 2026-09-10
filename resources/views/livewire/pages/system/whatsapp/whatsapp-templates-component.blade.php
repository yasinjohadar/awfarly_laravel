<div>
    {{-- Purpose assignment --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">{{__('pages/system/whatsapp.content.templates.assignment.title')}}</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">{{__('pages/system/whatsapp.content.templates.assignment.description')}}</p>

            <form wire:submit.prevent="saveAssignments">
                <div class="form-row">
                    @foreach($purposes as $purpose => $labelKey)
                        <div class="col-md-4 form-group">
                            <label>{{__($labelKey)}}</label>
                            <select wire:model.defer="purposeSelections.{{ $purpose }}" class="form-control">
                                <option value="">{{__('pages/system/whatsapp.content.templates.assignment.none')}}</option>
                                @foreach($templates as $template)
                                    @if($template->is_active || (string)$template->id === $purposeSelections[$purpose])
                                        <option value="{{ $template->id }}">
                                            {{ $template->name }}{{ !$template->is_active ? ' ('.__('pages/system/whatsapp.content.templates.inactive_badge').')' : '' }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveAssignments">
                    {{__('pages/system/whatsapp.content.templates.assignment.save')}}
                </button>
            </form>
        </div>
    </div>

    {{-- Templates list --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">{{__('pages/system/whatsapp.content.templates.title', ['count' => count($templates)])}}</h5>
            <button type="button" wire:click="openCreateModal" class="btn btn-sm btn-success">
                <i class="icon-plus3 mr-1"></i> {{__('pages/system/whatsapp.content.templates.add')}}
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr>
                        <th style="width:180px">{{__('pages/system/whatsapp.content.templates.table.name')}}</th>
                        <th>{{__('pages/system/whatsapp.content.templates.table.preview')}}</th>
                        <th style="width:100px">{{__('pages/system/whatsapp.content.templates.table.status')}}</th>
                        <th class="text-right" style="width:110px">{{__('pages/system/whatsapp.content.templates.table.actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($templates as $template)
                        <tr>
                            <td class="font-weight-semibold">{{ $template->name }}</td>
                            <td class="small text-muted" style="white-space:pre-line">{{ $template->render($sampleVars) }}</td>
                            <td>
                                <span class="badge badge-{{ $template->is_active ? 'success' : 'secondary' }}">
                                    {{ $template->is_active ? __('pages/system/whatsapp.content.templates.active_badge') : __('pages/system/whatsapp.content.templates.inactive_badge') }}
                                </span>
                            </td>
                            <td class="text-right text-nowrap">
                                <button type="button" wire:click="openEditModal({{ $template->id }})"
                                        title="{{__('pages/system/whatsapp.content.templates.table.edit')}}"
                                        class="btn btn-sm btn-outline-primary">
                                    <i class="icon-pencil7"></i>
                                </button>
                                <button type="button" wire:click="deleteTemplate({{ $template->id }})"
                                        onclick="return confirm('{{__('pages/system/whatsapp.content.templates.table.delete_confirm')}}')"
                                        title="{{__('pages/system/whatsapp.content.templates.table.delete')}}"
                                        class="btn btn-sm btn-outline-danger">
                                    <i class="icon-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                {{__('pages/system/whatsapp.content.templates.empty')}}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Create/edit modal --}}
    @if($showFormModal)
        <div class="modal fade show" style="display:block" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editing_id ? __('pages/system/whatsapp.content.templates.modal.edit_title') : __('pages/system/whatsapp.content.templates.modal.create_title') }}
                        </h5>
                        <button type="button" class="close" wire:click="closeFormModal"><span>&times;</span></button>
                    </div>
                    <form wire:submit.prevent="saveTemplate">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>{{__('pages/system/whatsapp.content.templates.modal.name')}}</label>
                                <input type="text" wire:model.defer="form_name"
                                       class="form-control @error('form_name') is-invalid @enderror">
                                @error('form_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{$message}}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>{{__('pages/system/whatsapp.content.templates.modal.content')}}</label>
                                <textarea wire:model="form_content" rows="5"
                                          class="form-control @error('form_content') is-invalid @enderror"></textarea>
                                @error('form_content')
                                    <span class="invalid-feedback" role="alert"><strong>{{$message}}</strong></span>
                                @enderror

                                <div class="mt-2">
                                    @foreach(\App\Models\WhatsApp\WhatsAppMessageTemplate::availablePlaceholders() as $placeholder => $description)
                                        <span class="badge badge-light border mr-1" title="{{ $description }}" style="cursor:help">{{ '{'.$placeholder.'}' }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="d-block">{{__('pages/system/whatsapp.content.templates.modal.preview')}}</label>
                                <div class="p-3 bg-light rounded small" style="white-space:pre-line">{{ $formPreview !== '' ? $formPreview : '—' }}</div>
                            </div>

                            <div class="form-check mt-3">
                                <input type="checkbox" wire:model.defer="form_is_active" class="form-check-input" id="form_is_active">
                                <label class="form-check-label" for="form_is_active">{{__('pages/system/whatsapp.content.templates.modal.is_active')}}</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" wire:click="closeFormModal" class="btn btn-outline-secondary">
                                {{__('pages/system/whatsapp.content.templates.modal.cancel')}}
                            </button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveTemplate">
                                {{__('pages/system/whatsapp.content.templates.modal.save')}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
