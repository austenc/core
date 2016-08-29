@extends('core::layouts.default')

@section('content')
	{!! Form::model($instructor, ['route' => ['instructors.archived.update', $instructor->id], 'method' => 'POST']) !!}
	<div class="row">
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>{{ $instructor->fullname }} <small>{{ Lang::choice('core::terms.instructor', 1) }}</small></h1>
				</div>
				{!! HTML::backlink('instructors.index') !!}
			</div>

			{{-- Warnings --}}
			@include('core::warnings.archived')
	
			{{-- Identification --}}
			@include('core::person.partials.arch_identification', ['record' => $instructor])

			{{-- Contact --}}
			@include('core::person.partials.arch_contact', ['record' => $instructor])

			{{-- Students --}}
			@include('core::instructors.partials.students')

			{{-- Training Programs by Discipline --}}
			@include('core::instructors.partials.archived_programs')

			{{-- Other Roles --}}
			@include('core::users.other_roles', ['user' => $instructor->user, 'ignore' => 'Instructor'])

			{{-- Timestamps --}}
			@include('core::partials.timestamps', ['record' => $instructor])

			{{-- Comments --}}
			@include('core::partials.comments', ['record' => $instructor])
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			@include('core::instructors.sidebars.archived')
		</div>
	</div>
	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/instructors/edit.js') !!}
@stop