<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	@if($item->status == 'draft')
		{!! Form::open(['route' => ['testitems.activate', $item->id], 'class' => 'pull-right form-inline']) !!}
			<button type="submit" class="btn btn-warning btn-block" data-confirm="Are you sure you want to activate this item? <p class='text-danger'>This cannot be un-done and the item can then be used in live tests.">
				{!! Icon::warning_sign() !!} Activate
			</button>
		{!! Form::close() !!}

		<hr>
	@endif
</div>