<div class="form-group row event-date-section">
	<div class="col-md-6">
		{!! Form::label('test_date', 'Test Date #'.($i+1), array('class' => "test-date-label")) !!} @include('core::partials.required')
		<div class="input-group">
			{!! Form::text('test_date['.$i.']', $curr_date, ['data-provide' => 'datepicker', 'data-date-start-date' => '0d']) !!}
			<span class="input-group-addon">
				<a href="javascript:void(0);" class="btn-sm btn-link remove-clone-date" data-toggle="tooltip" title="Remove this Test Date">
					{!! Icon::minus() !!} 
				</a>
			</span>
		</div>
	</div>
	
	<div class="col-md-6">
		{!! Form::label('start_time', 'Start Time #'.($i+1), array('class' => "start-time-label")) !!} @include('core::partials.required')
		{!! Form::text('start_time['.$i.']', $curr_time, ['class' => 'timepicker']) !!}
	</div>
</div>