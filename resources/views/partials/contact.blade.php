<h3 id="contact-info">Contact</h3>
<div class="well">
	<div class="form-group">
		{{-- Generate Fake Email --}}
		@if( ! Form::isDisabled())
		<label for="email" class="control-label">
			<a data-href="{{ route('generate.email', $name) }}" id="gen-email" title="Generate Fake Email" data-toggle="tooltip">Email</a>
		</label> @include('core::partials.required')
		@else
		<label for="email" class="control-label">
			Email
		</label>
		@endif

		@if(isset($record))
			{!! Form::text('email', $record->user->email, ['id' => 'email']) !!}
		@else
			{!! Form::text('email', '', ['id' => 'email']) !!}
		@endif
		<span class="text-danger">{{ $errors->first('email') }}</span>
	</div>

	{{-- Main Phone --}}
	<div class="form-group">
		{!! Form::label('phone', 'Phone') !!} @include('core::partials.required')
		
		{{-- Is Unlisted? --}}
		@if($name == 'student')
			&nbsp;&nbsp;
			{!! Form::checkbox('is_unlisted', 1, (isset($record) ? $record->is_unlisted : false), ['id' => 'ckbIsUnlisted']) !!}
			<strong><small>Phone Unlisted</small></strong>
		@endif

		{!! Form::text('phone') !!}
		<span class="text-danger">{{ $errors->first('phone') }}</span>
	</div>

	{{-- Alt Phone --}}
	<div class="form-group">
		{!! Form::label('alt_phone', 'Alt Phone') !!}
		{!! Form::text('alt_phone') !!}
		<span class="text-danger">{{ $errors->first('alt_phone') }}</span>
	</div>

	{{-- Fax --}}
	@if($name == 'facility')
		<div class="form-group">
			{!! Form::label('fax', 'Fax') !!}
			{!! Form::text('fax') !!}
			<span class="text-danger">{{ $errors->first('fax') }}</span>
		</div>
	@endif
</div>