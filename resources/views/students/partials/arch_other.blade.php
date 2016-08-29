<h3 id="other-info">Other</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('gender', 'Gender') !!}
		<div>
			{{ $student->gender }}
		</div>
	</div>
	<div class="form-group">
		{!! Form::label('oral', 'Oral') !!}
		<div>
			{{ $student->is_oral ? 'Yes' : 'No' }}
		</div>
	</div>
</div>