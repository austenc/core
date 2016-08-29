<div class="alert alert-{{{ $item->statusClass }}}">
	{{ $item->statusText }}  
	@if($item->status == 'draft')
		{!! Form::open(['route' => ['testitems.activate', $item->id], 'class' => 'pull-right form-inline']) !!}
			<button type="submit" class="btn btn-warning" data-confirm="Activate this Testitem? Are you sure?<br><br><strong><p class='text-danger'>This cannot be un-done! After activation the Testitem can then be used in live tests.</p></strong>">{!! Icon::warning_sign() !!} Activate This Item</button>
		{!! Form::close() !!}
		<br><br>
	@endif
</div>