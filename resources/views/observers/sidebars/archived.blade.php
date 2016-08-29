<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	{!! Button::success(Icon::refresh().' Update')->submit() !!}

	{{-- Restore --}}
	@if(Auth::user()->can('person.restore'))
		<a href="{{ route('person.restore', ['observers', $observer->id]) }}" class="btn btn-warning" data-confirm="Restore this {{{ Lang::choice('core::terms.observer', 1) }}}?<br><br>Are you sure?">
			{!! Icon::leaf() !!} Restore
		</a>
	@endif
</div>