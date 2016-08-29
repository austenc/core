{!! Form::open(['route' => $route]) !!}
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title">Select Tables to Truncate</h4>
</div>
<div class="modal-body">
	<div class="alert alert-warning">
		<strong>Warning!</strong> Select the following tables you would like to clear. 
		All records will be deleted and auto incrementing values will be reset to 1. 
		Truncating tables may have a rippling effect.<br><br>
		If you are not fully aware of the consequences, click 'Close'.
	</div>
	<table class="table select-enemies-table">
		<thead>
			<tr>
				<th></th>
				<th>Table</th>
				<th># Records</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($tables as $table)
			<tr>
				<td class="col-md-1">{!! Form::checkbox('tables[]', $table) !!}</td>
				<td>{{ $table }}</td>
				<td>{{ isset($records[$table]) ? $records[$table] : '?' }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>	
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	<button type="submit" class="btn btn-danger">{!! Icon::warning_sign() !!} Truncate</button>
</div>
{!! Form::close() !!}