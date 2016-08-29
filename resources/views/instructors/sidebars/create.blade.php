<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	{!! Button::success(Icon::plus_sign().' Save New '.Lang::choice('core::terms.instructor', 1))->submit() !!}

	@if( ! App::environment('production') && Auth::user()->ability(['Admin'], []))
		<a data-href="{{ route('instructors.populate') }}" id="populate" class="btn btn-default">
			{!! Icon::tint() !!} Populate
		</a>
	@endif
</div>