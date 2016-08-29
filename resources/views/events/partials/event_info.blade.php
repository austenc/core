<h3 id="event-info">Event Info</h3>
<div class="well">
	@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<div class="form-group row">
		{{-- Discipline --}}
		<div class="col-md-12">
			{!! Form::label('discipline', 'Discipline') !!}
			{!! Form::text('discipline', $event->discipline->name, ['disabled']) !!}
			{!! Form::hidden('discipline_id', $event->discipline_id) !!}
		</div>
	</div>
	@endif

	<div class="form-group row">
		{{-- Date --}}
		<div class="col-md-6">
			{!! Form::label('test_date', 'Test Date') !!} @include('core::partials.required')
			@if($event->locked == 0 && Auth::user()->can('events.change_datetime'))
				{!! Form::text('test_date[0]', $event->test_date, ['class' => 'test-date-picker']) !!}
				<span class="text-danger">{{ $errors->first('test_date') }}</span>
			@else
				{!! Form::text('test_date[0]', $event->test_date, ['class' => 'test-date-picker', 'disabled']) !!}
			@endif
		</div>
		
		{{-- Time --}}
		<div class="col-md-6">
			{!! Form::label('start_time', 'Start Time') !!} @include('core::partials.required')
			@if($event->locked == 0 && Auth::user()->can('events.change_datetime'))
				{!! Form::text('start_time[0]', $event->getOriginal('start_time'), ['class' => 'timepicker']) !!}
				<span class="text-danger">{{ $errors->first('start_time') }}</span>
			@else
				{!! Form::text('start_time[0]', $event->start_time, ['disabled']) !!}
			@endif
		</div>
	</div>

	<hr>

	{{-- Additional Event Options --}}
	<div class="form-group">
		<div class="checkbox">
			<label>
			@if(Auth::user()->ability(['Admin', 'Staff'], []))
				{!! Form::checkbox('is_regional', true, $event->is_regional) !!} 
			@else
				{!! Form::checkbox('is_regional', true, $event->is_regional, ['disabled']) !!} 
				{!! Form::hidden('is_regional', true) !!}
			@endif
			{!! Icon::globe() !!} This is a <strong>{{ strtolower(Lang::get('core::events.regional')) }}</strong> event
			</label>
		</div>
		<span class="text-danger">{{ $errors->first('is_regional') }}</span>
	</div>

	<div class="form-group">
		<div class="checkbox">
			<label>
			@if(Auth::user()->ability(['Admin', 'Staff'], []))
				{!! Form::checkbox('is_paper', true, $event->is_paper) !!} 
			@else
				{!! Form::checkbox('is_paper', true, $event->is_paper, ['disabled']) !!}
				{!! Form::hidden('is_paper', true) !!}
			@endif
			{!! Icon::file() !!} This is a <strong>paper</strong> event
			</label>
		</div>
		<span class="text-danger">{{ $errors->first('is_paper') }}</span>
	</div>
</div>