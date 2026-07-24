<div>
    <div class="form-group">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setContactId', null)">{{__('pages/requests/contact-us/inquiry.content.back')}}</button>
        @if($contact->status ==='unread')
            <button title="Edit" @cannot('requests.contact.us') disabled
                    @endcannot wire:click="showConfirmModal({{ $contact_id }})"
                    class="btn btn-secondary mx-1">
                <i class="icon-eye"></i>
            </button>
        @endif
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/contact-us/inquiry.content.type')}}</div>
                    <div
                        class="col-md-10 font-weight-bold">{{__("pages/requests/contact-us/inquiry.content.types.$contact->type")}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/contact-us/inquiry.content.name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$contact->name}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/contact-us/inquiry.content.email')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$contact->email}}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/contact-us/inquiry.content.mobile')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$contact->mobile}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/contact-us/inquiry.content.whatsapp_mobile')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$contact->whatsappMobile}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/contact-us/inquiry.content.status')}}</div>
                    <div class="col-md-10 font-weight-bold">{{ucwords($contact->status)}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/contact-us/inquiry.content.created_at')}}</div>
                    <div
                        class="col-md-10 font-weight-bold">{{\Carbon\Carbon::make($contact->created_at)->format('Y-m-d h:i A')}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/requests/contact-us/inquiry.content.message')}}</div>
                <div class="text-secondary">
                    {!! $contact->message !!}
                </div>
            </div>
        </div>
    </div>
    @include('modals.requests.contact-us.read')
</div>
