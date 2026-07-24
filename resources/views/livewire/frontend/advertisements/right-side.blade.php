<div>
    <div class="font-weight-bold text-center">
        <h3>{{__('frontend/home/home.content.side-advertisements')}}</h3>
    </div>
    @forelse ($right_side as $advertisement)
        @if($advertisement->advertisement_url)
            @if($advertisement->getMedia('advertisements')->count() > 0)
                <a href="{{$advertisement->advertisement_url}}" target="_blank">
                    <img alt="{{$advertisement->getFirstMedia('advertisements')->name}}" class="img-fluid mb-3"
                         src="{{$advertisement->getFirstMediaUrl('advertisements')}}"/>
                </a>
            @endif
        @else
            @if($advertisement->getMedia('advertisements')->count() > 0)
                <img alt="{{$advertisement->getFirstMedia('advertisements')->name}}" class="img-fluid mb-3"
                     src="{{$advertisement->getFirstMediaUrl('advertisements')}}"/>
            @endif
        @endif
    @empty
        <a class="bg-primary text-primary-100 text-center d-block p-4 display-4 mb-3" href="{{route('contact-us.index', 'In-app advertising')}}">
            {{__('frontend/home/home.content.add-your-ad')}}
        </a>
        <a class="bg-secondary text-secondary-100 text-center d-block p-4 display-4 mb-3" href="{{route('contact-us.index', 'In-app advertising')}}">
            {{__('frontend/home/home.content.add-your-ad')}}
        </a>
        <a class="bg-indigo text-indigo-100 text-center d-block p-4 display-4 mb-3" href="{{route('contact-us.index', 'In-app advertising')}}">
            {{__('frontend/home/home.content.add-your-ad')}}
        </a>
    @endforelse
</div>
