@extends('core::layouts.default')

@section('content')
	<div class="row">
        <div class="row">
			<div class="col-xs-8">
				<h3>
					Import Knowledge Testitems
					<small>Exam must have subjects loaded prior to import</small>
				</h3>
			</div>
			<div class="col-xs-4 valign-heading">
				@if(Auth::user()->ability(['Admin', 'Staff'], []))
					<a href="{{ route('truncate.testitems') }}" data-toggle="modal" data-target="#truncate" title="Clear Testitem tables" class="btn btn-sm btn-danger pull-right">
						{!! Icon::warning_sign() !!} Truncate
					</a>
				@endif
			</div>
		</div>
		<div class="well">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Test</th>
						<th>Subjects</th>
						<th>Filename</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				@foreach ($exams as $exam)
					{!! Form::open(['route' => array('import.knowledge', $exam->id), 'files' => true]) !!}
					<tr>
						<td>{{ $exam->name }}</td>
						<td>{{ $exam->subjects->count() }}</td>
						<td>{!! Form::file('file', '', ['id' => 'file', 'class' => 'btn']) !!}</td>
						<td>
							@if($exam->subjects->count() > 0)
							<button type="submit" name="submit" class="pull-right btn-sm btn btn-success">
			            	@else
							<button type="submit" name="submit" class="disabled pull-right btn-sm btn btn-success">
			            	@endif
			            		{!! Icon::arrow_up() !!} Import
			            	</button>
						</td>
					</tr>
					{!! Form::close() !!}
				@endforeach
				</tbody>
			</table>
		</div>

		<div class="row">
			<div class="col-xs-8">
				<h3>Import Skills</h3>
			</div>
			<div class="col-xs-4 valign-heading">
				@if(Auth::user()->ability(['Admin', 'Staff'], []))
					<a href="{{ route('truncate.skills') }}" data-toggle="modal" data-target="#truncate" title="Clear Skill tables" class="btn btn-sm btn-danger pull-right">
						{!! Icon::warning_sign() !!} Truncate
					</a>
				@endif
			</div>
		</div>
		<div class="well">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Test</th>
						<th>Type</th>
						<th>Filename</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				@foreach ($skills as $skill)
					{!! Form::open(['route' => array('import.skill.tasks', $skill->id), 'files' => true]) !!}
					<tr>
						<td rowspan="3">{{ $skill->name }}</td>
						<td>
							Tasks 
							<a href="{{ route('import.task.help') }}" data-toggle="modal" class="btn-link" data-target="#help" title="Import Task information">
								{!! Icon::info_sign() !!}
							</a>
						</td>
						<td>
	               			{!! Form::file('file', '', ['id' => 'file', 'class' => 'btn']) !!}
						</td>
						<td>
							<button type="submit" name="submit" class="pull-right btn-sm btn btn-success">
				            	{!! Icon::arrow_up() !!} Import
				            </button>
						</td>
					</tr>
					{!! Form::close() !!}

					{!! Form::open(['route' => array('import.skill.setups', $skill->id), 'files' => true]) !!}
					<tr>
						<td>
							Setups
							<a href="{{ route('import.setup.help') }}" data-toggle="modal" class="btn-link" data-target="#help" title="Import Setup information">
								{!! Icon::info_sign() !!}
							</a>
						</td>
						<td>
	               			{!! Form::file('file', '', ['id' => 'file', 'class' => 'btn']) !!}
						</td>
						<td>
							<button type="submit" name="submit" class="pull-right btn-sm btn btn-success">
				            	{!! Icon::arrow_up() !!} Import
				            </button>
						</td>
					</tr>
					{!! Form::close() !!}

					{!! Form::open(['route' => array('import.skill.steps', $skill->id), 'files' => true]) !!}
					<tr>
						<td>
							Steps
							<a href="{{ route('import.step.help') }}" data-toggle="modal" class="btn-link" data-target="#help" title="Import Steps information">
								{!! Icon::info_sign() !!}
							</a>
						</td>
						<td>
	               			{!! Form::file('file', '', ['id' => 'file', 'class' => 'btn']) !!}
						</td>
						<td>
							<button type="submit" name="submit" class="pull-right btn-sm btn btn-success">
				            	{!! Icon::arrow_up() !!} Import
				            </button>
						</td>
					</tr>
					{!! Form::close() !!}
				@endforeach
				</tbody>
			</table>
		</div>

		<div class="row">
			<div class="col-xs-8">
				<h3>Import for Training</h3>
			</div>
			<div class="col-xs-4 valign-heading">
				@if(Auth::user()->ability(['Admin', 'Staff'], []))
					<a href="{{ route('truncate.trainings') }}" data-toggle="modal" data-target="#truncate" title="Clear Training tables" class="btn btn-sm btn-danger pull-right">
						{!! Icon::warning_sign() !!} Truncate
					</a>
				@endif
			</div>
		</div>
		<div class="well">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Training</th>
						<th>Type</th>
						<th>Filename</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				@foreach ($trainings as $training)
					{!! Form::open(['route' => array('import.instructors', $training->id), 'files' => true]) !!}
					<tr>
						<td rowspan="2">{{ $training->name }}</td>
						<td>
							{{ Lang::choice('core::terms.instructor', 2) }}
							<a href="{{ route('import.instructor.help') }}" data-toggle="modal" class="btn-link" data-target="#help" title="Import {{{ Lang::choice('core::terms.instructor', 2) }}} information">
								{!! Icon::info_sign() !!}
							</a>
						</td>
						<td>
	               			{!! Form::file('file', '', ['id' => 'file', 'class' => 'btn']) !!}
						</td>
						<td>
							<button type="submit" name="submit" class="pull-right btn-sm btn btn-success">
				            	{!! Icon::arrow_up() !!} Import
				            </button>
						</td>
					</tr>
					{!! Form::close() !!}

					{!! Form::open(['route' => array('import.facilities', $training->id), 'files' => true]) !!}
					<tr>
						<td>
							{{ Lang::choice('core::terms.facility_training', 2) }}
							<a href="{{ route('import.facility.help') }}" data-toggle="modal" class="btn-link" data-target="#help" title="Import {{{ Lang::choice('core::terms.instructor', 2) }}} information">
								{!! Icon::info_sign() !!}
							</a>
						</td>
						<td>
	               			{!! Form::file('file', '', ['id' => 'file', 'class' => 'btn']) !!}
						</td>
						<td>
							<button type="submit" name="submit" class="pull-right btn-sm btn btn-success">
				            	{!! Icon::arrow_up() !!} Import
				            </button>
						</td>
					</tr>
					{!! Form::close() !!}
				@endforeach
				</tbody>
			</table>
		</div>

	</div>

	{!! HTML::modal('truncate') !!}
	{!! HTML::modal('help') !!}
@stop