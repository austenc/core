<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	{!! Button::success(Icon::refresh().' Update')->submit()->block() !!}

	@if(Auth::user()->can('facilities.activate'))
		<a href="{{ route('facilities.activate', $facility->id) }}" class="btn btn-warning" data-confirm="Restore this record?<br><br>Are you sure?">
			{!! Icon::leaf() !!} Restore
		</a>
	@endif
</div>