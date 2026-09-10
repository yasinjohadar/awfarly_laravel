<div>
    @if(!$activeInstanceName)
        <div class="alert alert-warning">{{__('pages/system/whatsapp.content.groups.no_instance')}}</div>
    @elseif($loadError)
        <div class="alert alert-danger">{{ $loadError }}</div>
    @elseif(!$selectedGroupJid)
        {{-- Groups list --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">{{__('pages/system/whatsapp.content.groups.title', ['count' => count($groups)])}}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                        <tr>
                            <th>{{__('pages/system/whatsapp.content.groups.table.name')}}</th>
                            <th>{{__('pages/system/whatsapp.content.groups.table.jid')}}</th>
                            <th>{{__('pages/system/whatsapp.content.groups.table.members')}}</th>
                            <th class="text-right">{{__('pages/system/whatsapp.content.groups.table.actions')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($groups as $group)
                            <tr>
                                <td>
                                    {{ $group['subject'] }}
                                    @if($group['announce'])
                                        <span class="badge badge-secondary ml-1">{{__('pages/system/whatsapp.content.groups.table.announce')}}</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $group['jid'] }}</td>
                                <td>{{ $group['size'] ?? '—' }}</td>
                                <td class="text-right">
                                    <button type="button"
                                            wire:click="selectGroup('{{ $group['jid'] }}', '{{ addslashes($group['subject']) }}')"
                                            class="btn btn-sm btn-outline-primary">
                                        {{__('pages/system/whatsapp.content.groups.table.open')}}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    {{__('pages/system/whatsapp.content.groups.empty')}}
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        {{-- Selected group: members + send --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">{{ $selectedGroupName }} ({{ count($members) }})</h5>
                <button type="button" wire:click="backToList" class="btn btn-sm btn-outline-secondary">
                    <i class="icon-arrow-right13 mr-1"></i> {{__('pages/system/whatsapp.content.groups.back')}}
                </button>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="sendToGroup" class="form-row align-items-end mb-4">
                    <div class="col-md-9 form-group mb-md-0">
                        <label>{{__('pages/system/whatsapp.content.groups.message_to_group')}}</label>
                        <textarea wire:model.defer="group_message" rows="2" class="form-control @error('group_message') is-invalid @enderror"></textarea>
                        @error('group_message')<span class="invalid-feedback"><strong>{{$message}}</strong></span>@enderror
                    </div>
                    <div class="col-md-3 form-group mb-md-0">
                        <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled" wire:target="sendToGroup">
                            <i class="icon-paperplane mr-1"></i> {{__('pages/system/whatsapp.content.groups.send')}}
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                        <tr>
                            <th>{{__('pages/system/whatsapp.content.groups.table.phone')}}</th>
                            <th>{{__('pages/system/whatsapp.content.groups.table.role')}}</th>
                            <th class="text-right">{{__('pages/system/whatsapp.content.groups.table.actions')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($members as $member)
                            <tr>
                                <td>{{ $member['phone'] ?? '—' }}</td>
                                <td>
                                    @if($member['is_admin'])
                                        <span class="badge badge-primary">{{__('pages/system/whatsapp.content.groups.table.admin')}}</span>
                                    @else
                                        <span class="text-muted">{{__('pages/system/whatsapp.content.groups.table.member')}}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <button type="button"
                                            wire:click="openMemberMessage('{{ $member['jid'] }}', '{{ $member['phone'] }}')"
                                            class="btn btn-sm btn-outline-primary">
                                        {{__('pages/system/whatsapp.content.groups.table.message')}}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    {{__('pages/system/whatsapp.content.groups.no_members')}}
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Send-to-member modal --}}
    @if($showMemberMessageModal)
        <div class="modal fade show" style="display:block" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $member_message_name }}</h5>
                        <button type="button" class="close" wire:click="closeMemberMessage"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="sendToMember">
                            <div class="form-group">
                                <textarea wire:model.defer="member_message_text" rows="4"
                                          class="form-control @error('member_message_text') is-invalid @enderror"></textarea>
                                @error('member_message_text')<span class="invalid-feedback"><strong>{{$message}}</strong></span>@enderror
                            </div>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="sendToMember">
                                <i class="icon-paperplane mr-1"></i> {{__('pages/system/whatsapp.content.groups.send')}}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
