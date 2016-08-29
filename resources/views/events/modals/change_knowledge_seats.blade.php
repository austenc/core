{!! Form::open(['route' => array('events.knowledge.change_seats', $event->id, $exam->id)]) !!}
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title">Change Knowledge Seats - {{ $exam->name }}</h4>
	</div>

	{{ $errors->first('seats') }}
	<div class="modal-body">
		<ul class="list-group">
			<li class="list-group-item">
    			<span class="badge alert-danger">{{ $min_seats }}</span>
    			Minimum
  			</li>
  			<li class="list-group-item">
    			<span class="badge alert-success">{{ $exam->pivot->open_seats }}</span>
    			Current
  			</li>
  			<li class="list-group-item">
    			<span class="badge alert-danger">{{ $max_seats }}</span>
    			Maximum
  			</li>
		</ul>

		<div class="form-group">
			{!! Form::label('seats', 'New Total Seats') !!}
			{!! Form::text('seats', '', ['class' => 'input-sm']) !!}
		</div>
	</div>

	<div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		<button type="submit" class="btn btn-success">Update Seats</button>
	</div>
{!! Form::close() !!}