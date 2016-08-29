<h3>Testing Team</h3>
<div class="well" id="pending-test-team-div">
	
	{{-- Observer --}}
	<div class="form-group">
		{!! Form::label('observer_id', Lang::choice('core::terms.observer', 1)) !!} 
		@if( ! empty($event->test_date))
			@include('core::partials.required')
			{!! Form::select('observer_id', $observers, ($event->observer_id ? $event->observer_id.'|observer' : '')) !!}
			<span class="text-danger">{{ $errors->first('observer_id') }}</span>
		@else
			{!! Form::select('observer_id', [0 => 'Select Test Date'], '', ['disabled', 'class' => 'test-team-sel']) !!}
		@endif
	</div>

	{{-- Proctor --}}
	<div class="form-group">
		{!! Form::label('proctor_id', Lang::choice('core::terms.proctor', 1)) !!}
		@if( ! empty($event->test_date))
			@if($event->proctor_type == 'Observer')
				{!! Form::select('proctor_id', $proctors, '-1|observer', ['class' => 'disable-first']) !!}
			@else
				{!! Form::select('proctor_id', $proctors, $event->proctor_id.'|proctor', ['class' => 'disable-first']) !!}
			@endif
			<span class="text-danger">{{ $errors->first('proctor_id') }}</span>
		@else
			{!! Form::select('proctor_id', [0 => 'Select Test Date'], '', ['disabled', 'class' => 'test-team-sel']) !!}
		@endif
	</div>

	{{-- Actor --}}
	<div class="form-group">
		{!! Form::label('actor_id', Lang::choice('core::terms.actor', 1)) !!}
		@if( ! empty($event->test_date))
			@if($event->actor_type == 'Observer')
				{!! Form::select('actor_id', $actors, '-1|observer', ['class' => 'disable-first']) !!}
			@else
				{!! Form::select('actor_id', $actors, $event->actor_id.'|actor', ['class' => 'disable-first']) !!}
			@endif
			<span class="text-danger">{{ $errors->first('actor_id') }}</span>
		@else
			{!! Form::select('actor_id', [0 => 'Select Test Date'], '', ['disabled', 'class' => 'test-team-sel']) !!}
		@endif
	</div>

	<div id="obs-is-mentor">
		<hr>

		{{-- Additional Event Options --}}
		<div class="form-group">
			<div class="checkbox">
				<label>{!! Form::checkbox('is_mentor', true, $event->is_mentor) !!} {!! Icon::bullhorn() !!} {{ Lang::choice('core::terms.observer', 1) }} is a <strong>mentor</strong></label>
			</div>
			<span class="text-danger">{{ $errors->first('is_mentor') }}</span>
		</div>
	</div>
</div>