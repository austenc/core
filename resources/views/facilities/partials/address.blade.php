<h3 id="address-info">Address</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('address', 'Address') !!} @include('core::partials.required')
		{!! Form::text('address') !!}
		<span class="text-danger">{{ $errors->first('address') }}</span>
	</div>
	<div class="form-group row">
		<div class="col-md-4">
			{!! Form::label('zip', 'Zipcode') !!} @include('core::partials.required') <small class="text-muted">Tab for City/State complete</small>
			{!! Form::text('zip') !!}
			<span class="text-danger">{{ $errors->first('zip') }}</span>
		</div>
		<div class="col-md-6">
			{!! Form::label('city', 'City') !!} @include('core::partials.required')
			{!! Form::text('city') !!}
			<span class="text-danger">{{ $errors->first('city') }}</span>
		</div>
		<div class="col-md-2">
			{!! Form::label('state', 'State') !!} @include('core::partials.required') <small class="text-muted">Like '{{ Config::get('core.client.abbrev') }}'</small>
			{!! Form::text('state') !!}
			<span class="text-danger">{{ $errors->first('state') }}</span>
		</div>
	</div>
</div>
		
<h3 id="address-ship-info">Mailing Address</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('mail_address', 'Address') !!}
		{!! Form::text('mail_address') !!}
		<span class="text-danger">{{ $errors->first('mail_address') }}</span>
	</div>
	<div class="form-group row">
		<div class="col-md-4">
			{!! Form::label('mail_zip', 'Zipcode') !!} <small class="text-muted">Tab for City/State complete</small>
			{!! Form::text('mail_zip') !!}
			<span class="text-danger">{{ $errors->first('mail_zip') }}</span>
		</div>
		<div class="col-md-6">
			{!! Form::label('mail_city', 'City') !!} 
			{!! Form::text('mail_city') !!}
			<span class="text-danger">{{ $errors->first('mail_city') }}</span>
		</div>
		<div class="col-md-2">
			{!! Form::label('mail_state', 'State') !!} <small class="text-muted">Like '{{ Config::get('core.client.abbrev') }}'</small>
			{!! Form::text('mail_state') !!}
			<span class="text-danger">{{ $errors->first('mail_state') }}</span>
		</div>
	</div>
</div>