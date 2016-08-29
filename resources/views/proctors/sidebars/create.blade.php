<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	{!! Button::success(Icon::plus_sign().' Save New '.Lang::choice('core::terms.proctor', 1))->submit() !!}

	@if( ! App::environment('production') && Auth::user()->isRole('Admin'))
		<a data-href="{{ route('proctors.populate') }}" id="populate" class="btn btn-default">
			{!! Icon::tint() !!} Populate
		</a>
	@endif
</div>