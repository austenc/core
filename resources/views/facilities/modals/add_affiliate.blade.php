{!! Form::open(['route' => ['facilities.affiliate.store']]) !!}
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title">Add Affiliate - {{ $discipline->name }}</h4>
	</div>
	<div class="modal-body">
		<div class="form-group row">
			<div class="col-md-12">
				{!! Form::label('affiliate_id', Lang::choice('core::terms.facility_training', 2)) !!} @include('core::partials.required')
				{!! Form::select('affiliate_id', [0 => 'Select Program'] + $affiliatedOpts) !!}
			</div>
		</div>
	</div>
	<div class="modal-footer">
		{!! Button::success('Add')->submit() !!}
		<a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
	</div>
	{!! Form::hidden('facility_id', $facility->id) !!}
	{!! Form::hidden('discipline_id', $discipline->id) !!}
{!! Form::close() !!}