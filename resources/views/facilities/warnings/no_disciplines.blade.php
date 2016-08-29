@if($facility->disciplines->isEmpty())
	<div class="alert alert-warning">
		{!! Icon::flag()  !!}<strong>No Active Disciplines</strong> Login capabilities are disabled
	</div>
@endif