@extends('core::layouts.default')

@section('content')
<form class="form-horizontal verify-demographics-form">
	<div class="col-md-9">
		<div class="row hidden-print">
			<div class="col-xs-8">
				<h1>{{ Lang::choice('core::terms.student', 1) }} Verification</h1>
			</div>
		</div>

		<h4>Identification</h4>
		<div class="well">
			<table class="table table-striped table-readonly">
				<tr>
					<td>
						<label class="control-label">Name</label>
					</td>
					<td>{{ $student->commaName }}</td>
				</tr>
				<tr>
					<td>
						<label class="control-label">Gender</label>
					</td>
					<td>{{ $student->gender }}</td>
				</tr>
				<tr>
					<td>
						<label class="control-label">Date of Birth</label>
					</td>
					<td>{{ $student->birthdate }}</td>
				</tr>

				@if(Auth::user()->ability(['Admin', 'Staff'], []))
					<tr>
						<td>
							<label class="control-label">SSN</label>
						</td>
						<td>{{ $student->plain_ssn }}</td>
					</tr>
				@endif

				<tr>
					<td>
						<label class="control-label">Phone</label>
					</td>
					<td>{{ $student->phone }}</td>
				</tr>

				@if($student->alt_phone)
					<tr>
						<td>
							<label class="control-label">Alternate Phone</label>
						</td>
						<td>{{ $student->alt_phone }}</td>
					</tr>
				@endif

				<tr>
					<td>
						<label class="control-label">Address</label>
					</td>
					<td>
						{{ $student->address }} <br>
						{{ $student->city }}, {{ $student->state }} {{ $student->zip }}
					</td>
				</tr>
			</table>
		</div> {{-- .well --}}
			
		<h4>Login Information</h4>
		<div class="well">
			<table class="table table-striped table-readonly">
				<tr>
					<td>
						<label class="control-label">Email</label>
					</td>
					<td>{{ $student->user->email }}</td>
				</tr>
				<tr>
					<td>
						<label class="control-label">Username</label>
					</td>
					<td>{{ $student->user->username }}</td>
				</tr>
				<tr>
					<td>
						<label class="control-label">Password</label>
					</td>
					<td>
			  			@if(isset($password))
			  				{{ $password }}
			  			@else
							<span class="text-muted">[encrypted - cannot be shown]</span>
			  			@endif
					</td>
				</tr>

			</table>
		</div>

		<h4>Initial Training</h4>
		<div class="well">
			<table class="table table-striped table-readonly">
				<tr>
					<td>
						<label class="control-label">Training</label>
					</td>
					<td>{{ $initialTraining->training->name }}</td>
				</tr>
				<tr>
					<td>
						<label class="control-label">Status</label>
					</td>
					<td>{{ ucfirst($initialTraining->status) }}</td>
				</tr>

				@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
					<tr>
						<td>
							<label class="control-label">Discipline</label>
						</td>
						<td>{{ $initialTraining->discipline->name }}</td>
					</tr>
				@endif

				<tr>
					<td>
						<label class="control-label">{{ Lang::choice('core::terms.facility_training', 1) }}</label>
					</td>
					<td>{{ $initialTraining->facility->name }}</td>
				</tr>

				@if(Auth::user()->userable_type != 'Instructor')
					<tr>
						<td>
							<label class="control-label">{{ Lang::choice('core::terms.instructor', 1) }}</label>
						</td>
						<td>{{ $initialTraining->instructor->fullName }}</td>
					</tr>
				@endif

				<tr>
					<td>
						<label class="control-label">Started On</label>
					</td>
					<td>{{ $initialTraining->started }}</td>
				</tr>

				@if($initialTraining->status != 'attending')
					<tr>
						<td>
							<label class="control-label">Ended On</label>
						</td>
						<td>{{ $initialTraining->ended }}</td>
					</tr>
				@endif
			</table>
		</div> {{-- .well --}}

		{{-- Agreement / Signature --}}
		<div class="form-group noborder text-center visible-print-block">
			<div class="checkbox">
			  <label>
			    <input type="checkbox" name="information_correct" value="">
			    I certify that I have reviewed this information and have informed my {{ Lang::choice('core::terms.instructor', 1) }} of necessary corrections.
			  </label>
			</div>
		</div>

		<div class="form-group noborder visible-print-block">
			<div class="col-xs-6">
				<div class="signature-line">Applicant Signature</div>
			</div>
			<div class="col-xs-6">
				<div class="signature-line">Date of Signature</div>
			</div>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			<a href="{{ route('students.edit', $student->id) }}" class="btn btn-success">{!! Icon::arrow_right() !!} Continue to Student</a>
			<a href="javascript:window.print()" class="btn btn-info"><span class="glyphicon glyphicon-print"></span> Print</a>
		</div>
	</div>
</form>
@stop