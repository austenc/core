@extends('core::layouts.default')

@section('content')
	{!! Form::model($skillexam, ['route' => ['skillexams.update', $skillexam->id], 'method' => 'PUT']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-md-8">
				<h1>Edit Skill Exam</h1>
			</div>
			{!! HTML::backlink('skillexams.index') !!}
		</div>

		{{-- Warnings --}}
		@include('core::skills.exams.warnings.no_skilltasks')
		
		{{-- Basic Info --}}
		<h3 id="exam-info">Basic Info</h3>
		<div class="well">
			<div class="form-group row">
				<div class="col-md-8">
					{!! Form::label('name', 'Name') !!} @include('core::partials.required')
					{!! Form::text('name') !!}
					<span class="text-danger">{{ $errors->first('name') }}</span>
				</div>

				<div class="col-md-4">
					{!! Form::label('abbrev', 'Abbrev') !!}
					{!! Form::text('abbrev') !!}
					<span class="text-danger">{{ $errors->first('abbrev') }}</span>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('discipline_id', 'Discipline') !!}
					{!! Form::text('discipline_id', $skillexam->discipline->name, ['disabled']) !!}
					{!! Form::hidden('discipline_id', $skillexam->discipline->id) !!}
				</div>
			</div>

			<hr>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('slug', 'Slug') !!}
					{!! Form::text('slug', '', ['disabled']) !!}
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('max_attempts', 'Max Attempts') !!}
					{!! Form::text('max_attempts') !!}
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('price', 'Price') !!}
				    <div class="input-group">
				        <div class="input-group-addon">$</div>
				        {!! Form::text('price', $skillexam->price, ['class' => 'form-control']) !!}
				    </div>
					<span class="text-danger">{{ $errors->first('price') }}</span>
				</div>
			</div>
		</div>

		{{-- Skill Tasks --}}
		<h3 id="exam-tasks">Skill Tasks</h3>
		<div class="well">
			<table class="table table-striped row-sel" id="skilltests-table">
				<thead>
					<tr>
						<th>#</th>
						<th>Title</th>
						<th>Weight</th>
						<th>Minimum</th>
						<th>Steps</th>
						<th>Setups</th>
					</tr>
				</thead>
				<tbody>
				@foreach($skillexam->tasks as $task)
					@if($task->status == 'draft')
					<tr class="warning">
					@elseif($task->status == 'active')
					<tr class="success">
					@else
					<tr class="danger">
					@endif
						<td>
							<span class="lead text-muted">{{ $task->id }}</span>
						</td>

						<td>
							<a href="{{ route('tasks.edit', $task->id) }}" target="_blank">
								{{ $task->title }}
							<a><br>

							@if($task->status == 'active')
							<span class="label label-success">
							@elseif($task->status == 'archived')
							<span class="label label-danger">
							@else
							<span class="label label-warning">
							@endif
								{{ ucfirst($task->status) }}
							</span>
						</td>

						<td class="monospace">{{ $task->weight }}</td>
						<td class="monospace">{{ $task->minimum }}</td>
						<td class="monospace">{{ $task->steps->count() }}</td>
						<td class="monospace">{{ $task->setups->count() }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		</div>

		{{-- Skill Tests --}}
		<h3 id="exam-tests">Skill Tests</h3>
		<div class="well">
			<table class="table table-striped row-sel" id="skilltests-table">
				<thead>
					<tr>
						<th>#</th>
						<th>Header</th>
						<th>Minimum</th>
						<th>Tasks</th>
					</tr>
				</thead>
				<tbody>
				@foreach($skillexam->tests as $test)
					@if($test->status == 'draft')
					<tr class="warning">
					@elseif($test->status == 'active')
					<tr class="success">
					@else
					<tr class="danger">
					@endif
						<td>
							<span class="lead text-muted">{{ $test->id }}</span>
						</td>

						<td>
							<a href="{{ route('skills.edit', $test->id) }}" target="_blank">
								{{ $test->header }}
							</a><br>

							@if($test->status == 'active')
							<span class="label label-success">
							@elseif($test->status == 'archived')
							<span class="label label-danger">
							@else
							<span class="label label-warning">
							@endif
								{{ ucfirst($test->status) }}
							</span>
						</td>

						<td class="monospace">{{ $test->minimum }}</td>
						<td class="monospace">{{ $test->tasks->count() }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		</div>
	
		<h3 id="exam-requirements">Requirements</h3>
		<div class="well">
			<h4>Trainings</h4>
			<table class="table table-striped" id="req-training-table">
				<tbody>
					@foreach ($discipline->training as $tr)
						@if(in_array($tr->id, $skillexam->required_trainings->lists('id')->all()))
						<tr class="warning">
						@else
						<tr>
						@endif
							<td>
								@if(in_array($tr->id, $skillexam->required_trainings->lists('id')->all()))
								<a title="Required" data-toggle="tooltip">{!! Icon::star() !!}</a>
								@endif
								{{ $tr->name }}
							</td>
							<td>{!! Form::select('req_training['.$tr->id.']', [0 => 'Not Required', 1 => 'Required'], in_array($tr->id, $skillexam->required_trainings->lists('id')->all())) !!}</td>
						</tr>
					@endforeach
				</tbody>
			</table>

			<hr>

			<h4>Knowledge Exams</h4>
			<table class="table table-striped" id="req-exam-table">
				<tbody>
					@foreach ($discipline->exams as $ex)
						@if(in_array($ex->id, $skillexam->required_exams->lists('id')->all()) || in_array($ex->id, $skillexam->corequired_exams->lists('id')->all()))
						<tr class="warning">
						@else
						<tr>
						@endif
							<td>
								@if(in_array($ex->id, $skillexam->required_exams->lists('id')->all()))
								<a title="Required" data-toggle="tooltip">{!! Icon::star() !!}</a>
								@elseif(in_array($ex->id, $skillexam->corequired_exams->lists('id')->all()))
								<a title="Corequired" data-toggle="tooltip">{!! Icon::star() !!}</a>
								@endif
								{{ $ex->name }}
							</td>
							<td>
								@if(in_array($ex->id, $skillexam->required_exams->lists('id')->all()))
									{!! Form::select('req_exam['.$ex->id.']', [0 => 'Not Required', 1 => 'Required', 2 => 'Corequired'], 1) !!}
								@elseif(in_array($ex->id, $skillexam->corequired_exams->lists('id')->all()))
									{!! Form::select('req_exam['.$ex->id.']', [0 => 'Not Required', 1 => 'Required', 2 => 'Corequired'], 2) !!}
								@else
									{!! Form::select('req_exam['.$ex->id.']', [0 => 'Not Required', 1 => 'Required', 2 => 'Corequired'], 0) !!}
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		<h3>Notes</h3>
		<div class="well">
			<div class="form-group">
				<textarea name="comments" id="comments" class="form-control">{{ $skillexam->comments }}</textarea>
			</div>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::skills.exams.sidebars.edit')
	</div>

	{!! Form::close() !!}
@stop