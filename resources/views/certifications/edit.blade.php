@extends('core::layouts.default')

@section('content')
	<div class="row">
		{!! Form::model($cert, ['route' => ['certifications.update', $cert->id], 'method' => 'PUT']) !!}
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>Edit Certification</h1>
				</div>
				{!! HTML::backlink('certifications.index') !!}
			</div>

			<h3>Basic Info</h3>
			<div class="well">
				<div class="form-group row">
					<div class="col-md-8">
						{!! Form::label('name', 'Name') !!} @include('core::partials.required')
						{!! Form::text('name') !!}
						<span class="text-danger">{{ $errors->first('name') }}</span>
					</div>

					<div class="col-md-4">
						{!! Form::label('abbrev', 'Abbrev') !!} @include('core::partials.required')
						{!! Form::text('abbrev') !!}
						<span class="text-danger">{{ $errors->first('abbrev') }}</span>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('discipline_id', 'Discipline') !!}
						{!! Form::text('discipline', $cert->discipline->name, ['disabled']) !!}
						{!! Form::hidden('discipline_id', $cert->discipline->id) !!}
					</div>
				</div>
			</div>

			<h3>Requirements</h3>
			<div class="well table-responsive">
				@if($discipline->exams->isEmpty())
				<h4>No Knowledge Exams under {{ $discipline->name }}</h4>
				@else
				<h4>Knowledge Exams</h4>
				@endif
				<table class="table table-striped" id="req-exam-table">
					<tbody>
						@foreach ($discipline->exams as $ex)
							@if(in_array($ex->id, $cert->required_exams->lists('id')->all()))
							<tr class="warning">
							@else
							<tr>
							@endif
								<td>
									@if(in_array($ex->id, $cert->required_exams->lists('id')->all()))
									<a title="Required" data-toggle="tooltip">{!! Icon::star() !!}</a>
									@endif
									{{ $ex->name }}
								</td>
								<td>
									{!! Form::select('req_exam_id['.$ex->id.']', [0 => 'Not Required', 1 => 'Required'], in_array($ex->id, $cert->required_exams->lists('id')->all())) !!}
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>

				<hr>

				@if($discipline->skills->isEmpty())
				<h4>No Skill Exams under {{ $discipline->name }}</h4>
				@else
				<h4>Skill Exams</h4>
				@endif
				<table class="table table-striped" id="req-exam-table">
					<tbody>
						@foreach ($discipline->skills as $sk)
							@if(in_array($sk->id, $cert->required_skills->lists('id')->all()))
							<tr class="warning">
							@else
							<tr>
							@endif
								<td>
									@if(in_array($sk->id, $cert->required_skills->lists('id')->all()))
									<a title="Required" data-toggle="tooltip">{!! Icon::star() !!}</a>
									@endif
									{{ $sk->name }}
								</td>
								<td>
									{!! Form::select('req_skill_id['.$sk->id.']', [0 => 'Not Required', 1 => 'Required'], in_array($sk->id, $cert->required_exams->lists('id')->all())) !!}
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			{{-- Students --}}
			<h3>{{ Lang::choice('core::terms.student', 2) }}</h3>
			<div class="well">
			@if($cert->students->isEmpty())
				No {{ Lang::choice('core::terms.student', 2) }}
			@else
				<table class="table table-striped" id="students-table">
					<thead>
						<tr>
							<th>Student</th>
							<th>Certified</th>
							<th>Expires</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($cert->students as $student)
							<tr>
								<td>
									<a class="btn btn-link" href="{{ route('students.edit', $student->id) }}">{{ $student->commaName }}</a>
								</td>
								<td>{{ $student->pivot->certified_at }}</td>
								<td>{{ $student->pivot->expires_at }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
			</div>

			<h3>Notes</h3>
			<div class="well">
				<div class="form-group">
					<textarea name="comments" id="comments" class="form-control">@if(Input::old('comments')){{ Input::old('comments') }}@else{{ $cert->comments }}@endif</textarea>
				</div>
			</div>
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
				{!! Button::success(Icon::refresh().' Update')->submit()->block() !!}
			</div>
		</div>
	{!! Form::close() !!}
@stop