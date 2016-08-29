{!! Form::open(['url' => route('students.attach_attempt_image', [$id, $type]), 'method' => 'POST', 'files' => true]) !!}	
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title">Attach Media</h4>
	</div>

	<div class="modal-body">
		<div class="alert alert-warning">
			<strong>Heads Up!</strong><br>
			If this test was taken at the same time as another test, the media will also be attached to that attempt.
		</div>
		<label>Choose a File</label>
		{!! Form::file('image') !!}
	</div>

	<div class="modal-footer">
		<button type="submit" class="btn btn-success">
			{!! Icon::upload() !!} Upload
		</button>
	</div>

	{!! Form::hidden('attempt_id', $id) !!}
	{!! Form::hidden('type', $type) !!}
{!! Form::close() !!}