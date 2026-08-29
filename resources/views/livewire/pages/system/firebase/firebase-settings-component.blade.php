<div>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">{{__('pages/system/firebase.content.status.title')}}</h5>
        </div>
        <div class="card-body">
            @if($project_id)
                <table class="table table-borderless mb-3">
                    <tbody>
                    <tr>
                        <th style="width: 220px;">{{__('pages/system/firebase.content.status.project_id')}}</th>
                        <td>{{$project_id}}</td>
                    </tr>
                    <tr>
                        <th>{{__('pages/system/firebase.content.status.client_email')}}</th>
                        <td>{{$client_email}}</td>
                    </tr>
                    <tr>
                        <th>{{__('pages/system/firebase.content.status.private_key_id')}}</th>
                        <td><code>{{$private_key_id}}</code></td>
                    </tr>
                    <tr>
                        <th>{{__('pages/system/firebase.content.status.file_path')}}</th>
                        <td class="text-break"><small class="text-muted">{{$currentPath}}</small></td>
                    </tr>
                    <tr>
                        <th>{{__('pages/system/firebase.content.status.key_fingerprint')}}</th>
                        <td>
                            <code>{{$key_fingerprint ?? '—'}}</code>
                            <small class="d-block text-muted">{{__('pages/system/firebase.content.status.key_fingerprint_hint')}}</small>
                        </td>
                    </tr>
                    <tr>
                        <th>{{__('pages/system/firebase.content.status.key_usable')}}</th>
                        <td>
                            @if($key_usable === true)
                                <span class="badge badge-success">{{__('pages/system/firebase.content.status.key_usable_yes')}}</span>
                            @elseif($key_usable === false)
                                <span class="badge badge-danger">{{__('pages/system/firebase.content.status.key_usable_no')}}</span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{__('pages/system/firebase.content.status.server_time')}}</th>
                        <td>
                            <code>{{$server_time_utc ?? '—'}}</code>
                            <small class="d-block text-muted">{{__('pages/system/firebase.content.status.server_time_hint')}}</small>
                        </td>
                    </tr>
                    </tbody>
                </table>
            @else
                <div class="alert alert-warning mb-3">
                    {{__('pages/system/firebase.content.status.no_credentials')}}
                </div>
            @endif

            <button type="button" wire:click="testConnection" wire:loading.attr="disabled" wire:target="testConnection"
                    class="btn btn-outline-teal">
                <span wire:loading.remove wire:target="testConnection">
                    <i class="icon-plugin mr-1"></i> {{__('pages/system/firebase.content.status.test_connection')}}
                </span>
                <span wire:loading wire:target="testConnection">
                    <i class="spinner-border spinner-border-sm mr-1"></i> {{__('pages/system/firebase.content.status.testing')}}
                </span>
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">{{__('pages/system/firebase.content.credentials.title')}}</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">{{__('pages/system/firebase.content.credentials.description')}}</p>

            <div class="alert alert-info">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                    <strong>{{__('pages/system/firebase.content.credentials.guide_title')}}</strong>
                    <a href="https://console.firebase.google.com/project/{{ $project_id ?: '_' }}/settings/serviceaccounts/adminsdk"
                       target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                        {{__('pages/system/firebase.content.credentials.guide_link')}}
                        <i class="icon-arrow-up-right ml-1"></i>
                    </a>
                </div>
                <ol class="mb-0 pl-3">
                    @foreach(__('pages/system/firebase.content.credentials.guide_steps') as $step)
                        <li>{{$step}}</li>
                    @endforeach
                </ol>
            </div>
            <form wire:submit.prevent="save">
                <div class="form-group row">
                    <label class="col-form-label col-lg-2"
                           for="input_project_id">{{__('pages/system/firebase.content.credentials.project_id')}}</label>
                    <div class="col-lg-10">
                        <input type="text" id="input_project_id" wire:model.defer="input_project_id"
                               class="form-control @error('input_project_id') is-invalid @enderror"/>
                        @error('input_project_id')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-lg-2"
                           for="input_client_email">{{__('pages/system/firebase.content.credentials.client_email')}}</label>
                    <div class="col-lg-10">
                        <input type="text" id="input_client_email" wire:model.defer="input_client_email"
                               class="form-control @error('input_client_email') is-invalid @enderror" dir="ltr"/>
                        @error('input_client_email')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-lg-2"
                           for="input_client_id">{{__('pages/system/firebase.content.credentials.client_id')}}</label>
                    <div class="col-lg-10">
                        <input type="text" id="input_client_id" wire:model.defer="input_client_id"
                               class="form-control @error('input_client_id') is-invalid @enderror" dir="ltr"/>
                        @error('input_client_id')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-lg-2"
                           for="input_private_key_id">{{__('pages/system/firebase.content.credentials.private_key_id')}}</label>
                    <div class="col-lg-10">
                        <input type="text" id="input_private_key_id" wire:model.defer="input_private_key_id"
                               class="form-control @error('input_private_key_id') is-invalid @enderror" dir="ltr"/>
                        @error('input_private_key_id')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-lg-2"
                           for="input_private_key">{{__('pages/system/firebase.content.credentials.private_key')}}</label>
                    <div class="col-lg-10">
                        <textarea id="input_private_key" wire:model.defer="input_private_key" rows="6" dir="ltr"
                                  placeholder="-----BEGIN PRIVATE KEY-----&#10;...&#10;-----END PRIVATE KEY-----"
                                  class="form-control @error('input_private_key') is-invalid @enderror"></textarea>
                        <small class="text-muted">{{__('pages/system/firebase.content.credentials.private_key_hint')}}</small>
                        @error('input_private_key')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" wire:loading.remove wire:target="save" wire:key="save"
                            class="btn btn-teal">
                        {{__('pages/system/firebase.content.credentials.submit')}}
                        <i class="icon-check ml-2"></i>
                    </button>
                    <i wire:loading wire:target="save" wire:key="saving"
                       class="spinner-border text-dark mr-2"></i>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{__('pages/system/firebase.content.test_notification.title')}}</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">{{__('pages/system/firebase.content.test_notification.description')}}</p>
            <form wire:submit.prevent="sendTestNotification">
                <div class="form-group row">
                    <label class="col-form-label col-lg-2"
                           for="test_token">{{__('pages/system/firebase.content.test_notification.token')}}</label>
                    <div class="col-lg-10">
                        <input type="text" id="test_token" wire:model.defer="test_token"
                               class="form-control @error('test_token') is-invalid @enderror"/>
                        @error('test_token')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-lg-2"
                           for="test_title">{{__('pages/system/firebase.content.test_notification.notification_title')}}</label>
                    <div class="col-lg-10">
                        <input type="text" id="test_title" wire:model.defer="test_title"
                               class="form-control @error('test_title') is-invalid @enderror"/>
                        @error('test_title')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-lg-2"
                           for="test_body">{{__('pages/system/firebase.content.test_notification.notification_body')}}</label>
                    <div class="col-lg-10">
                        <input type="text" id="test_body" wire:model.defer="test_body"
                               class="form-control @error('test_body') is-invalid @enderror"/>
                        @error('test_body')
                        <div class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" wire:loading.remove wire:target="sendTestNotification" wire:key="send"
                            class="btn btn-teal">
                        {{__('pages/system/firebase.content.test_notification.submit')}}
                        <i class="icon-paperplane ml-2"></i>
                    </button>
                    <i wire:loading wire:target="sendTestNotification" wire:key="sending"
                       class="spinner-border text-dark mr-2"></i>
                </div>
            </form>
        </div>
    </div>
</div>
