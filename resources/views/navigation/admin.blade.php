<ul class="sidebar-menu">
    {{-- People --}}
    @include('core::navigation.partials.people')

    {{-- Events --}}
    {!! HTML::nav('events', 'Events', 'calendar') !!}

    {{-- Scoring --}}
    @include('core::navigation.partials.scoring')

    {{-- Reports --}}
    {!! HTML::nav('reports', 'Reports', 'stats') !!}

    {{-- State Services --}}
    @if(View::exists('navigation.services'))
        @include('navigation.services')
    @endif

    {{-- Utilities --}}
    @include('core::navigation.partials.utilities')

    {{-- Testbank --}}
    @include('core::navigation.partials.testbank')

    {{-- Accounting --}}
    @include('core::navigation.partials.accounting')

    {{-- Settings --}}
    @include('core::navigation.partials.settings')
</ul>