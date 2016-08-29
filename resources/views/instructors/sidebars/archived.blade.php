<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	{!! Button::success(Icon::refresh().' Update')->submit() !!}

    {{-- Restore --}}
    @if(Auth::user()->can('person.restore'))
        <a href="{{ route('person.restore', ['instructors', $instructor->id]) }}" class="btn btn-warning" data-confirm="Restore this {{{ Lang::choice('core::terms.instructor', 1) }}}?<br><br>Are you sure?">
            {!! Icon::leaf() !!} Restore
        </a>
    @endif

    {{-- Remap & Delete --}}
    @if(Auth::user()->can('instructors.remap'))
        <a href="{{ route('instructors.remap', [$instructor->id]) }}" class="btn btn-danger">
            {!! Icon::scissors() !!} Remap &amp; Delete
        </a>
    @endif
</div>