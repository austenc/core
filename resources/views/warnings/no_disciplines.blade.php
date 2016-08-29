@if(Auth::user()->ability(['Admin', 'Staff'], []))
	@if($record->disciplines->isEmpty())
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>Disciplines</strong> No Disciplines associated with this record
	</div>
	@endif
@endif