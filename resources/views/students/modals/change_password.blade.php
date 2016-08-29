{!! Form::open(['route' => ['students.change_password', $student->id]]); !!}
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title">Change Password</h4>
</div>
<div class="modal-body">
	<div class="form-group">
		{!! Form::label('username', 'Username') !!}
		{!! Form::text('username', $student->user->username, ['disabled' => 'disabled']) !!}
		<span class="text-danger">{{ $errors->first('username') }}</span>			
	</div>
	<div class="form-group">
		{!! Form::label('email', 'Email') !!}
		{!! Form::text('email', $student->user->email, ['disabled' => 'disabled']) !!}
		<span class="text-danger">{{ $errors->first('email') }}</span>			
	</div>

	{{-- Password Fields --}}
	@include('core::partials.change_password')

</div>
<div class="modal-footer">
	<button type="submit" class="btn btn-success">Update</button>
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>
{!! Form::hidden('student_id', $student->id) !!}
{!! Form::close() !!}