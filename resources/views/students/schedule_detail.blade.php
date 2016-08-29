@extends('core::layouts.default')

@section('content')
	<div class="col-md-9">
		<div class="row">
			<div class="col-md-8">
				<h1>
					Scheduled Detail<span
				</h1>
			</div>
			<div class="col-md-4 back-link">
				<a href="{{ route('students.edit', $attempt->student->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to {{ Lang::choice('core::terms.student', 1) }}</a>
			</div>
		</div>

		<div class="well clearfix">
			<p class="lead">Attempt Record</p>
			<table class="table table-striped table-readonly">
				<tr>
					<td>
						<label class="control-label">{{ Lang::choice('core::terms.student', 1) }}</label>
					</td>
					<td>
						{{ $attempt->student->full_name }}
					</td>
				</tr>

				<tr>
					<td>
						<label class="control-label">
							Test Type
						</label>
					</td>
					<td>
						{{ ucfirst($type) }}
					</td>
				</tr>

				<tr>
					<td>
						<label class="control-label">
							@if($type == 'skill') Skill @endif Exam
						</label>
					</td>
					<td>
						{{ $type == 'skill' ? $attempt->skillexam->name : $attempt->exam->name }}
					</td>
				</tr>

			    <td>
					<label class="control-label">Status</label>
				</td>
		        <td>
					<span class="label label-info">Scheduled</span>
		        </td>

				{{-- Start Time --}}
				@include('core::testing.partials.start_end')
			</table>
		</div>

		{{-- Test Event --}}
		@include('core::testing.partials.detail_event', ['attempt' => $attempt])
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">

		</div>
	</div>
@stop