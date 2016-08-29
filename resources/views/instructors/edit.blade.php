@extends('core::layouts.default')

@section('content')
	{!! Form::model($instructor, ['route' => ['instructors.update', $instructor->id], 'method' => 'PUT']) !!}
	<div class="row">
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>{{ $instructor->fullname }} <small>{{ Lang::choice('core::terms.instructor', 1) }}</small></h1>
				</div>
				{!! HTML::backlink('instructors.index') !!}
			</div>

			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active">
					<a href="#instructor-info" aria-controls="instructor info" role="tab" data-toggle="tab">
						{!! Icon::info_sign() !!} {{ Lang::choice('core::terms.instructor', 1) }} Info
					</a>
				</li>

				{{-- Disciplines --}}
				@if( ! $instructor->disciplines->isEmpty())
					@foreach($instructor->disciplines as $disc)
						<li role="presentation">
							<a href="#instructor-discipline-{{{ strtolower($disc->abbrev) }}}" aria-controls="disciplines" role="tab" data-toggle="tab">
								{!! Icon::briefcase() !!} {{ $disc->name }}
							</a>
						</li>

						<li role="presentation">
							<a href="#instructor-discipline-{{{ strtolower($disc->abbrev) }}}-students" aria-controls="disciplines" role="tab" data-toggle="tab">
								{!! Icon::user() !!} {{ $disc->abbrev }} {{ Lang::choice('core::terms.student', 2) }}
							</a>
						</li>
					@endforeach
				@endif
			</ul>
			<div class="tab-content well">
			    <div role="tabpanel" class="tab-pane active" id="instructor-info">
			    	{{-- Warnings --}}
					@include('core::warnings.active_hold', ['hold' => $instructor->isHold])
					@include('core::warnings.active_lock', ['lock' => $instructor->isLocked])
					@include('core::warnings.multiple_roles', ['user' => $instructor->user])
					@include('core::warnings.fake_email', ['user' => $instructor->user])
					@include('core::instructors.warnings.expiration')
					@include('core::instructors.warnings.no_trainings')
					@include('core::instructors.warnings.no_programs')
			
					{{-- Identification --}}
					@include('core::instructors.partials.identification')

					{{-- Status --}}
					@include('core::partials.record_status', ['record' => $instructor])

					{{-- Contact --}}
					@include('core::partials.contact', ['name' => 'instructor', 'record' => $instructor])
					
					{{-- Address --}}
					@include('core::partials.address')
		
					{{-- Other Roles --}}
					@include('core::users.other_roles', ['user' => $instructor->user, 'ignore' => 'Instructor'])
					
					{{-- Login Info --}}
					@include('core::partials.login_info', ['record' => $instructor, 'name' => 'instructors'])

					{{-- Timestamps --}}
					@include('core::partials.timestamps', ['record' => $instructor])

					{{-- Comments --}}
					@include('core::partials.comments', ['record' => $instructor])
				</div>

				{{-- Disciplines --}}
				@if( ! $instructor->disciplines->isEmpty())
					@foreach($instructor->disciplines as $d)
						<div role="tabpanel" class="tab-pane" id="instructor-discipline-{{{ strtolower($d->abbrev) }}}">
							@include('core::instructors.partials.discipline', ['discipline' => $d])
						</div>

						{{-- Students --}}
					    <div role="tabpanel" class="tab-pane" id="instructor-discipline-{{{ strtolower($d->abbrev) }}}-students">
							@include('core::instructors.partials.discipline_students', [
								'discipline' => $d,
								'instructor' => $instructor,
								'students'   => $disciplineInfo[$d->id]['students']
							])
						</div>
					@endforeach
				@endif
			</div>
		</div>
		
		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			@include('core::instructors.sidebars.edit')
		</div>

	</div>
	{!! Form::close() !!}
	{!! HTML::modal('add-discipline') !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/instructors/edit.js') !!}
@stop