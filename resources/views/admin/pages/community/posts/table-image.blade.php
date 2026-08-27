@if($url)
    <a href="{{ $url }}" target="_blank">
        <img src="{{ $url }}" alt="post" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
    </a>
@else
    <span style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:6px;background:#f1f5f9;color:#94a3b8;">
        <i class="icon-image2"></i>
    </span>
@endif
