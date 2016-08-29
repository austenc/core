<h3 id="contact-info">Contact</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('don', 'Director of Nursing') !!}
		<div>
			{{ empty($facility->don) ? 'N/A' : $facility->don }}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('administrator', 'Administrator') !!}
		<div>
			{{ empty($facility->administrator) ? 'N/A' : $facility->administrator }}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('email', 'Email') !!}
		<div>
			{{ empty($facility->user->email) ? 'N/A' : $facility->user->email }}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('phone', 'Phone') !!}
		<div>
			{{ empty($facility->phone) ? 'N/A' : $facility->phone }}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('fax', 'Fax') !!}
		<div>
			{{ empty($facility->fax) ? 'N/A' : $facility->fax }}
		</div>
	</div>
</div>