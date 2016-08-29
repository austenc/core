@extends('core::layouts.default')

@section('content')
	{!! Form::model($student, ['route' => ['students.update', $student->id], 'method' => 'PUT']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>{{ $student->fullname }} <small>{{ Lang::choice('core::terms.student', 1) }}</small></h1>
			</div>
			{!! HTML::backlink('students.index') !!}
		</div>

		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active">
				<a href="#student-info" aria-controls="student info" role="tab" data-toggle="tab">
					{!! Icon::info_sign() !!} {{ Lang::choice('core::terms.student', 1) }} Info
				</a>
			</li>
			<li role="presentation">
				<a href="#student-trainings" aria-controls="trainings" role="tab" data-toggle="tab">
					{!! Icon::list_alt() !!} Trainings
				</a>
			</li>
			@if(Auth::user()->can('students.view_test_history') || (Auth::user()->can('students.view_exams') && $student->isActive))
				<li role="presentation">
					<a href="#student-testing" aria-controls="testing" role="tab" data-toggle="tab">
						{!! Icon::edit() !!} Testing
					</a>
				</li>
			@endif

			@if(Auth::user()->can('students.view_certs') && Config::get('core.certification.show'))
				<li role="presentation">
					<a href="#student-certifications" aria-controls="certifications" role="tab" data-toggle="tab">
						{!! Icon::education() !!} Certifications
					</a>
				</li>
			@endif

			@if( ! $student->adas->isEmpty())
				<li role="presentation">
					<a href="#student-adas" aria-controls="adas" role="tab" data-toggle="tab">
						{!! Icon::time() !!} ADAs
					</a>
				</li>
			@endif
		</ul>
		<div class="tab-content well">
		    <div role="tabpanel" class="tab-pane active" id="student-info">
				{{-- Warnings --}}
				@include('core::students.warnings.null_testform')
				@include('core::warnings.fake_email', ['user' => $student->user])
				@include('core::students.warnings.fake_ssn')
				@include('core::students.warnings.locked_account')
				@include('core::students.warnings.hold_account')
				@include('core::students.warnings.scheduled')
				@include('core::students.warnings.ready_to_schedule')
				@include('core::students.warnings.oral')
				@include('core::students.warnings.ada')

				{{-- Identification --}}
				@include('core::students.partials.identification')

				{{-- Other --}}
				@include('core::students.partials.other')

				{{-- Contact --}}
				@include('core::partials.contact', ['name' => 'student', 'record' => $student])

				{{-- Address --}}
				@include('core::partials.address')

				{{-- Account Status --}}
				@include('core::students.partials.account_status', ['holds' => $holds, 'locks' => $locks])

				{{-- Current Owner --}}
				@include('core::students.partials.current_owner')
		
				{{-- Login Info --}}
				@include('core::partials.login_info', ['record' => $student, 'name' => 'students'])

				{{-- Timestamps --}}
				@include('core::partials.timestamps', ['record' => $student])

				{{-- Comments --}}
				@include('core::partials.comments', ['record' => $student])
		    </div>

		    <div role="tabpanel" class="tab-pane" id="student-certifications">
				{{-- Certifications --}}
				@include('core::students.partials.certifications')
		    </div>

		    <div role="tabpanel" class="tab-pane" id="student-trainings">
				{{-- Trainings --}}
				@include('core::students.partials.trainings')
		    </div>

		    <div role="tabpanel" class="tab-pane" id="student-testing">
				{{-- Scheduling --}}
				@include('core::students.partials.scheduling')

				{{-- Test Attempts --}}
				@include('core::students.partials.test_attempts')

				{{-- Rescheduled --}}
				@include('core::students.partials.rescheduled')
		    </div>

		    <div role="tabpanel" class="tab-pane" id="student-adas">
				{{-- ADA --}}
				@include('core::students.partials.ada')
			</div>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::students.sidebars.edit')
	</div>
	{!! Form::close() !!}

	{{-- Modals --}}
	{!! HTML::modal('attach-media') !!}
@stop
@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/students/status.js') !!}
	<script>
		function changePaymentStatus(attemptID, field){
			var type = $(field).closest('tr').find('small.testtype').text();
			var value = $(field).val();
			$.ajax({
				url: '/accounting/payment/update/' + attemptID + '/' + type.toLowerCase() + '/' + value,
				data: 'json',
				success: function(result){
					$(".flash-messages").append('<div class="alert alert-success alert-dismissable fade in"><button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button><div class="messages"><ul><li>Payment Status Updated.</li></ul></div></div></div>');
				}
			});
		}
	</script>
@stop