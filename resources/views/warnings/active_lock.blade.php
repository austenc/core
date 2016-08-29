@if(Auth::user()->ability(['Admin', 'Staff'], []) && $lock)
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>Lock</strong> Account login has been disabled
	</div>
@endif