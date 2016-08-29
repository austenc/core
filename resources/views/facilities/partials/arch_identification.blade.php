<h3 id="facility-info">Identification</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('name', 'Name') !!}
		<div>
			{{ $facility->name }}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('username', 'Username') !!}
		<div>
			{{ $facility->user->username }}
		</div>
	</div>
	
	<div class="form-group">
		{!! Form::label('license', 'License') !!}
		<div>
			{{ $facility->license }}
		</div>
	</div>
</div>