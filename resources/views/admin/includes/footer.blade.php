<!-- Footer -->
<div class="navbar navbar-expand-lg navbar-light border-bottom-0 border-top">
    <div class="text-center d-lg-none w-100">
        <button type="button" class="navbar-toggler dropdown-toggle" data-toggle="collapse"
                data-target="#navbar-footer">
            <i class="icon-unfold mr-2"></i>
            {{__('pages/footer.title')}}
        </button>
    </div>

    <div class="navbar-collapse collapse" id="navbar-footer">
        <span class="navbar-text">
            <small
                class="text-black-100 h6">{{__('pages/footer.copyright', ['now' => now()->year, 'name' => Settings::Get('site.name')])}}</small>
        </span>
    </div>
</div>
<!-- /footer -->
