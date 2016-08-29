{!! Form::open(['route' => ['facilities.discipline.store', $facility->id]]) !!}
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title">Add Discipline</h4>
	</div>
	<div class="modal-body">
		<div class="form-group row">
			<div class="col-md-12">
				{!! Form::label('discipline_id', 'Discipline') !!} @include('core::partials.required')
				{!! Form::select('discipline_id', [0 => 'Select Discipline'] + $disciplines->lists('name', 'id')->all()) !!}
			</div>
		</div>
	</div>
	<div class="modal-footer">
		{!! Button::success('Add')->submit() !!}
		<a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
	</div>
{!! Form::close() !!}