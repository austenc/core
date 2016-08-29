<h3 id="test-site-info">{{ Lang::choice('core::terms.facility_testing', 1) }}</h3>
<div class="well">
	<div class="form-group row">
		<div class="col-md-12">
			{!! Form::label('testsite_name', 'Name') !!}
			{!! Form::hidden('facility_id', $event->facility->id, ['id' => 'facility_id']) !!}
			
			@if(Auth::user()->can('facilities.manage'))
				<div class="input-group">
					{!! Form::text('testsite_name', $event->facility->name, ['disabled']) !!}
					<div class="input-group-addon">
						<a href="{{ route('facilities.edit', $event->facility_id) }}">{!! Icon::pencil() !!}</a>
					</div>
				</div>
			@else
				{!! Form::text('testsite_name', $event->facility->name, ['disabled']) !!}
			@endif
		</div>
	</div>

	<div class="form-group row">
		<div class="col-md-12">
			{!! Form::label('address', 'Address') !!}
			{!! Form::text('address', $event->facility->address, ['disabled']) !!}
		</div>
	</div>

	<div class="form-group row">
		<div class="col-md-6"> 
			{!! Form::label('city', 'City') !!}
			{!! Form::text('city', $event->facility->city, ['disabled']) !!}
		</div>
		<div class="col-md-4"> 
			{!! Form::label('zip', 'Zipcode') !!}
			{!! Form::text('zip', $event->facility->zip, ['disabled']) !!}
		</div>
		<div class="col-md-2"> 
			{!! Form::label('state', 'State') !!}
			{!! Form::text('state', $event->facility->state, ['disabled']) !!}
		</div>
	</div>
</div>