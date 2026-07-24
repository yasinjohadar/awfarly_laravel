<div>
    <div class="font-weight-bold text-center">
        <h3>{{__('frontend/home/home.content.side-advertisements')}}</h3>
    </div>
    @forelse ($left_side as $advertisement)
        @if($advertisement->advertisement_url)
            <a href="{{$advertisement->advertisement_url}}" target="_blank">
                @if($advertisement->getMedia('advertisements')->count() > 0)
                    <img alt="{{$advertisement->getFirstMedia('advertisements')->name}}" class="img-fluid mb-3"
                         src="{{$advertisement->getFirstMediaUrl('advertisements')}}"/>
                @endif
            </a>
        @else
            @if($advertisement->getMedia('advertisements')->count() > 0)
                <img alt="{{$advertisement->getFirstMedia('advertisements')->name}}" class="img-fluid mb-3"
                     src="{{$advertisement->getFirstMediaUrl('advertisements')}}"/>
            @endif
        @endif
    @empty
        <a class="border bg-primary text-primary-100 text-center d-block p-4 display-4 mb-3" href="{{route('contact-us.index', 'In-app advertising')}}">
            {{__('frontend/home/home.content.add-your-ad')}}
        </a>
        <a class="border bg-secondary text-secondary-100 text-center d-block p-4 display-4 mb-3" href="{{route('contact-us.index', 'In-app advertising')}}">
            {{__('frontend/home/home.content.add-your-ad')}}
        </a>
        <a class="border bg-indigo text-indigo-100 text-center d-block p-4 display-4 mb-3" href="{{route('contact-us.index', 'In-app advertising')}}">
            {{__('frontend/home/home.content.add-your-ad')}}
        </a>
    @endforelse
</div>
