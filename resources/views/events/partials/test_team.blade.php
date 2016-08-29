<h3 id="test-team-info">Test Team</h3>
<div class="well">
	{{-- Observer --}}
	<div class="form-group row">
		<div class="col-md-12">
			{!! Form::label('observer', Lang::choice('core::terms.observer', 1)) !!}
			@if($event->is_mentor)
				<small class="text-danger"><strong>- Mentoring</strong></small>
			@endif
			{!! Form::hidden('observer_id', $event->observer->id) !!}
			@if(Auth::user()->can('observers.manage'))
				<div class="input-group">
					{!! Form::text('observer', $event->observer->full_name, ['disabled']) !!}
					<div class="input-group-addon">
			        	<a href="{{ route('observers.edit', $event->observer->id) }}">{!! Icon::pencil() !!}</a>
			        </div>
				</div>
			@else
				{!! Form::text('observer', $event->observer->full_name, ['disabled']) !!}
			@endif
		</div>
	</div>

	{{-- Proctor --}}
	@if($event->proctor)
		<div class="form-group row">
			<div class="col-md-12">
				{!! Form::label('proctor', Lang::choice('core::terms.proctor', 1)) !!}
				@if($event->proctor_type != 'Proctor')
					<small class="text-danger"><strong>- {{ Lang::choice('core::terms.observer', 1) }} filling in</strong></small>
				@endif

				@if(Auth::user()->can('proctors.manage'))
					<div class="input-group">
				        {!! Form::text('proctor', $event->proctor->full_name, ['disabled']) !!}
				        <div class="input-group-addon">
				        	{{-- Proctor is Proctor --}}
				        	@if($event->proctor_type == 'Proctor')
				        		<a href="{{ route('proctors.edit', $event->proctor_id) }}">{!! Icon::pencil() !!}</a>
				        	{{-- Proctor is Observer --}}
				        	@else
								<a href="{{ route('observers.edit', $event->proctor_id) }}">{!! Icon::pencil() !!}</a>
				        	@endif
				        </div>
				    </div>
				@else
					{!! Form::text('proctor', $event->proctor->full_name, ['disabled']) !!}
				@endif
			</div>
		</div>
	@endif

	{{-- Actor --}}
	@if($event->actor)
		<div class="form-group row">
			<div class="col-md-12">
				{!! Form::label('actor', Lang::choice('core::terms.actor', 1)) !!}
				@if(isset($event->actor_type) && $event->actor_type != 'Actor')
					<small class="text-danger"><strong>- {{ Lang::choice('core::terms.observer', 1) }} filling in</strong></small>
				@endif

				@if(Auth::user()->can('actors.manage'))
					<div class="input-group">
				        {!! Form::text('actor', $event->actor->full_name, ['disabled']) !!}
				        <div class="input-group-addon">
							{{-- Actor is Actor --}}
				        	@if($event->actor_type == 'Actor')
				        		<a href="{{ route('actors.edit', $event->actor_id) }}">{!! Icon::pencil() !!}</a>
				        	{{-- Actor is Observer --}}
				        	@else
								<a href="{{ route('observers.edit', $event->actor_id) }}">{!! Icon::pencil() !!}</a>
				        	@endif
				       	</div>
				    </div>
				@else
					{!! Form::text('actor', $event->actor->full_name, ['disabled']) !!}
				@endif
			</div>
		</div>
	@endif

	@if(Auth::user()->ability(['Admin', 'Staff'], []))
		<hr>

		{{-- Additional Event Options --}}
		<div class="form-group">
			<div class="checkbox">
				<label>{!! Form::checkbox('is_mentor', true, $event->is_mentor) !!} {!! Icon::bullhorn() !!} {{ Lang::choice('core::terms.observer', 1) }} is a <strong>mentor</strong></label>
			</div>
			<span class="text-danger">{{ $errors->first('is_mentor') }}</span>
		</div>
	@else
		{!! Form::hidden('is_mentor', $event->is_mentor) !!}
	@endif
</div>