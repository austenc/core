<h3 id="identification">Identification</h3>
<div class="well">
	{{-- Name --}}
	@include('core::partials.name')

	{{-- License --}}
	<div class="form-group">
		{!! Form::label('license', 'License') !!}
		{!! Form::text('license') !!}
		<span class="text-danger">{{ $errors->first('license') }}</span>
	</div>

	{{-- DOB --}}
	@include('core::partials.birthdate')

	{{-- Gender --}}
	@include('core::partials.gender')
</div>