<div class="well">
	<p class="lead">Timestamps</p>
	<div class="form-group row">
		<div class="col-xs-12">
			{!! Form::label('curr_date', 'Test Date') !!} @include('core::partials.required')
			{!! Form::text('curr_date', $attempt->start_date, ['class' => 'date-picker', 'data-provide' => 'datepicker']) !!}
		</div>
	</div>
	
	<hr>

	<div class="form-group row">
		<div class="col-xs-12">
			{!! Form::label('start_time', 'Start Time') !!} @include('core::partials.required')
			{!! Form::text('start_time', $attempt->start_time, ['class' => 'time-picker']) !!}
		</div>
	</div>
	<div class="form-group row">
		<div class="col-xs-12">
			{!! Form::label('end_time', 'End Time') !!} @include('core::partials.required')
			{!! Form::text('end_time', $attempt->end_time, ['class' => 'time-picker']) !!}
		</div>
	</div>
</div>