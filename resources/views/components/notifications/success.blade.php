<div x-data="{open: false, message:null}"
     x-on:success.window="open = true; message = event.detail; setTimeout(() => {open = true}, 1000)"
     x-show="open"
     x-transition:enter="transform ease-out duration-500 transition"
     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-500"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="alert alert-success"
     role="alert">
        <p class="font-semibold">{{Session::get('message')}}</p>
</div>
