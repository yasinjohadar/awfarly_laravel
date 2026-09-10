<div>
    <div class="card mb-4">
        <div class="card-body pb-0">
            @if($rotationPoolCount > 0)
                <div class="alert alert-info py-2 small">
                    {{__('pages/system/whatsapp.content.send.rotation_hint', ['count' => $rotationPoolCount])}}
                </div>
            @else
                <div class="alert alert-warning py-2 small">
                    {{__('pages/system/whatsapp.content.send.no_rotation_hint')}}
                </div>
            @endif

            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a href="#" wire:click.prevent="setType('text')" class="nav-link {{ $type === 'text' ? 'active' : '' }}">
                        {{__('pages/system/whatsapp.content.send.tabs.text')}}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" wire:click.prevent="setType('media')" class="nav-link {{ $type === 'media' ? 'active' : '' }}">
                        {{__('pages/system/whatsapp.content.send.tabs.media')}}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" wire:click.prevent="setType('advanced')" class="nav-link {{ $type === 'advanced' ? 'active' : '' }}">
                        {{__('pages/system/whatsapp.content.send.tabs.advanced')}}
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            @if($type === 'text')
                <form wire:submit.prevent="sendText">
                    <div class="form-group">
                        <label>{{__('pages/system/whatsapp.content.send.to')}}</label>
                        <input type="text" wire:model.defer="text_to" class="form-control @error('text_to') is-invalid @enderror" placeholder="9665xxxxxxxx">
                        @error('text_to')<span class="invalid-feedback"><strong>{{$message}}</strong></span>@enderror
                    </div>
                    <div class="form-group">
                        <label>{{__('pages/system/whatsapp.content.send.message')}}</label>
                        <textarea wire:model.defer="text_message" rows="4" class="form-control @error('text_message') is-invalid @enderror"></textarea>
                        @error('text_message')<span class="invalid-feedback"><strong>{{$message}}</strong></span>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="sendText">
                        <i class="icon-paperplane mr-1"></i> {{__('pages/system/whatsapp.content.send.submit')}}
                    </button>
                </form>
            @elseif($type === 'media')
                <form wire:submit.prevent="sendMedia">
                    <div class="form-group">
                        <label>{{__('pages/system/whatsapp.content.send.to')}}</label>
                        <input type="text" wire:model.defer="media_to" class="form-control @error('media_to') is-invalid @enderror" placeholder="9665xxxxxxxx">
                        @error('media_to')<span class="invalid-feedback"><strong>{{$message}}</strong></span>@enderror
                    </div>
                    <div class="form-group">
                        <label>{{__('pages/system/whatsapp.content.send.media_type')}}</label>
                        <select wire:model.defer="media_type" class="form-control">
                            <option value="image">{{__('pages/system/whatsapp.content.send.media_types.image')}}</option>
                            <option value="video">{{__('pages/system/whatsapp.content.send.media_types.video')}}</option>
                            <option value="audio">{{__('pages/system/whatsapp.content.send.media_types.audio')}}</option>
                            <option value="document">{{__('pages/system/whatsapp.content.send.media_types.document')}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{__('pages/system/whatsapp.content.send.media_url')}}</label>
                        <input type="text" wire:model.defer="media_url" class="form-control @error('media_url') is-invalid @enderror" placeholder="https://...">
                        @error('media_url')<span class="invalid-feedback"><strong>{{$message}}</strong></span>@enderror
                    </div>
                    <div class="form-group">
                        <label>{{__('pages/system/whatsapp.content.send.media_file_name')}}</label>
                        <input type="text" wire:model.defer="media_file_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__('pages/system/whatsapp.content.send.media_caption')}}</label>
                        <textarea wire:model.defer="media_caption" rows="2" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="sendMedia">
                        <i class="icon-paperplane mr-1"></i> {{__('pages/system/whatsapp.content.send.submit')}}
                    </button>
                </form>
            @else
                <form wire:submit.prevent="sendAdvanced">
                    <div class="form-group">
                        <label>{{__('pages/system/whatsapp.content.send.advanced_type')}}</label>
                        <select wire:model.defer="advanced_type" class="form-control">
                            <option value="buttons">Buttons</option>
                            <option value="list">List</option>
                            <option value="poll">Poll</option>
                            <option value="location">Location</option>
                            <option value="contact">Contact</option>
                            <option value="sticker">Sticker</option>
                            <option value="status">Status</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{__('pages/system/whatsapp.content.send.to')}}</label>
                        <input type="text" wire:model.defer="advanced_to" class="form-control @error('advanced_to') is-invalid @enderror" placeholder="9665xxxxxxxx">
                        @error('advanced_to')<span class="invalid-feedback"><strong>{{$message}}</strong></span>@enderror
                    </div>
                    <div class="form-group">
                        <label>{{__('pages/system/whatsapp.content.send.advanced_payload')}}</label>
                        <textarea wire:model.defer="advanced_payload" rows="6" class="form-control @error('advanced_payload') is-invalid @enderror"
                                  placeholder='{"title": "...", "buttons": [...]}'></textarea>
                        @error('advanced_payload')<span class="invalid-feedback"><strong>{{$message}}</strong></span>@enderror
                        <small class="form-text text-muted">{{__('pages/system/whatsapp.content.send.advanced_hint')}}</small>
                    </div>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="sendAdvanced">
                        <i class="icon-paperplane mr-1"></i> {{__('pages/system/whatsapp.content.send.submit')}}
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
