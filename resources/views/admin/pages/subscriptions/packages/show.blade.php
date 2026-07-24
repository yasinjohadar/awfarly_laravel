<div>
    <div class="form-group">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setPackageId', null)">{{__('pages/subscriptions/packages/show.content.back')}}</button>
        <button title="Edit" @cannot('packages.edit') disabled
                @endcannot  wire:click="showEditModal({{ $package_id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-pencil7"></i>
        </button>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/packages/show.content.product_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$package->product_id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/packages/show.content.name_en')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$package->name_en}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/packages/show.content.name_ar')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$package->name_ar}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/packages/show.content.is_visible')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$package->is_visible ? __('pages/subscriptions/packages/show.content.boolean.yes') :
                        __('pages/subscriptions/packages/show.content.boolean.no')}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/packages/show.content.is_active')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$package->is_active ? __('pages/subscriptions/packages/show.content.boolean.yes') :
                        __('pages/subscriptions/packages/show.content.boolean.no')}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/packages/show.content.is_trial')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$package->is_trial ? __('pages/subscriptions/packages/show.content.boolean.yes') :
                        __('pages/subscriptions/packages/show.content.boolean.no')}}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/packages/show.content.price')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$package->current_price}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/packages/show.content.old_price')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$package->full_price}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/packages/show.content.duration')}}</div>
                    <div class="col-md-10 font-weight-bold">{{ucwords($package->duration)}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/packages/show.content.maximum_posts')}}</div>
                    <div class="col-md-10 font-weight-bold">{{ucwords($package->maximum_posts)}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="row">
                    <div class="col-md-6">
                        <div
                            class="font-weight-bold">{{__('pages/subscriptions/packages/show.content.description_en')}}</div>
                        <div class="text-secondary">
                            {!! $package->description_en !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div
                            class="font-weight-bold">{{__('pages/subscriptions/packages/show.content.description_ar')}}</div>
                        <div class="text-secondary">
                            {!! $package->description_ar !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="row">
                    <div class="col-md-6">
                        <div
                            class="font-weight-bold">{{__('pages/subscriptions/packages/show.content.specifications_en')}}</div>
                        <div class="text-secondary">
                            <ul>
                                @forelse($package->specifications_en as $specifications)
                                    <li>{{$specifications}}</li>
                                @empty
                                    <li>{{__('pages/subscriptions/packages/show.content.no-specifications')}}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div
                            class="font-weight-bold">{{__('pages/subscriptions/packages/show.content.specifications_ar')}}</div>
                        <div class="text-secondary">
                            <ul>
                                @forelse($package->specifications_ar as $specifications)
                                    <li>{{$specifications}}</li>
                                @empty
                                    <li>{{__('pages/subscriptions/packages/show.content.no-specifications')}}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('modals.subscriptions.packages.edit')
</div>
