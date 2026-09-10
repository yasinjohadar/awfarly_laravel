<div>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">{{__('pages/system/whatsapp.content.settings.title')}}</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">{{__('pages/system/whatsapp.content.settings.description')}}</p>

            <form wire:submit.prevent="save">
                <div class="form-group">
                    <label for="evolution_base_url">{{__('pages/system/whatsapp.content.settings.base_url')}}</label>
                    <input type="text" id="evolution_base_url" wire:model.defer="evolution_base_url"
                           class="form-control @error('evolution_base_url') is-invalid @enderror"
                           placeholder="https://evolution.example.com"
                           autocomplete="off">
                    @error('evolution_base_url')
                        <span class="invalid-feedback" role="alert"><strong>{{$message}}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="evolution_api_key">{{__('pages/system/whatsapp.content.settings.api_key')}}</label>
                    <input type="password" id="evolution_api_key" wire:model.defer="evolution_api_key"
                           class="form-control @error('evolution_api_key') is-invalid @enderror"
                           placeholder="{{ $has_api_key ? __('pages/system/whatsapp.content.settings.api_key_placeholder_set') : __('pages/system/whatsapp.content.settings.api_key_placeholder_unset') }}"
                           autocomplete="new-password">
                    @error('evolution_api_key')
                        <span class="invalid-feedback" role="alert"><strong>{{$message}}</strong></span>
                    @enderror
                    <small class="form-text text-muted">{{__('pages/system/whatsapp.content.settings.api_key_hint')}}</small>
                </div>

                <div class="form-group">
                    <label for="evolution_instance_name">{{__('pages/system/whatsapp.content.settings.default_instance')}}</label>
                    <input type="text" id="evolution_instance_name" wire:model.defer="evolution_instance_name"
                           class="form-control @error('evolution_instance_name') is-invalid @enderror"
                           autocomplete="off">
                    @error('evolution_instance_name')
                        <span class="invalid-feedback" role="alert"><strong>{{$message}}</strong></span>
                    @enderror
                    <small class="form-text text-muted">{{__('pages/system/whatsapp.content.settings.default_instance_hint')}}</small>
                </div>

                @if($rotationPoolCount > 0)
                    <div class="alert alert-info">
                        {{__('pages/system/whatsapp.content.settings.rotation_pool_hint', ['count' => $rotationPoolCount])}}
                        <a href="{{route('admin.system.whatsapp.instances')}}">{{__('pages/system/whatsapp.content.settings.rotation_pool_link')}}</a>
                    </div>
                @endif

                <div class="d-flex" style="gap: .5rem">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                        {{__('pages/system/whatsapp.content.settings.save')}}
                    </button>
                    <button type="button" wire:click="testConnection" wire:loading.attr="disabled" wire:target="testConnection"
                            class="btn btn-outline-teal">
                        <span wire:loading.remove wire:target="testConnection">
                            <i class="icon-plugin mr-1"></i> {{__('pages/system/whatsapp.content.settings.test_connection.action')}}
                        </span>
                        <span wire:loading wire:target="testConnection">
                            <i class="spinner-border spinner-border-sm mr-1"></i> {{__('pages/system/whatsapp.content.settings.test_connection.testing')}}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
