<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	{!! Button::success(Icon::plus_sign().' Continue, Choose Test Team')->submit() !!}

	@if(Auth::user()->ability(['Admin', 'Staff'], []))
		<button type="submit" class="btn btn-warning" name="create_as_pending" value="true">
			{!! Icon::warning_sign().' Create as Pending Event' !!}
		</button>
	@endif
</div>