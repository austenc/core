 <!-- Main Menu -->
@if(Auth::check())
    @if($userMenu)
        @include($userMenu)
    @endif
@endif
