@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'exams.index', 'method' => 'get']) !!}
	<div class="row">
		<div class="col-md-9">
			<p class="lead">Knowledge Exams</p>
			<div class="form-group">
				{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
				<div class="input-group">
					{!! Form::text('search', Input::get('search'), 
					['placeholder' => 'Search for Exams', 'autofocus' => 'autofocus']) !!}
					<span class="input-group-btn">
						<button class="btn btn-info" type="submit">
							{!! Icon::search() !!} <span class="sr-only">Search</span>
						</button>
      				</span>
      			</div>
      		</div>

			<div class="well">
	      		<table class="table table-striped">
					<thead>
						<tr>
							<th>{!! Sorter::link('exams.index', 'Name', ['sort' => 'name']) !!}</th>
							<th>Discipline</th>
							<th>Requirements</th>
						</tr>
					</thead>
					<tbody>
						@foreach($exams as $exam)
							<tr>
								<td>
									<a href="{{ route('exams.edit', $exam->id) }}">
										{{ $exam->name }}
									</a><br>
									<small>{{ $exam->abbrev }}</small>
								</td>

								<td>{{ $exam->discipline->name }}</td>

								<td>
									@foreach($exam->required_trainings as $reqTraining)
										{{ $reqTraining->name }}<br>
										<small>Training</small><br>
									@endforeach

									@foreach($exam->required_exams as $reqExam)
										{{ $reqExam->name }}<br>
										<small>Exam</small><br>
									@endforeach

									@foreach($exam->required_skills as $reqSkill)
										{{ $reqSkill->name }}<br>
										<small>{{ ucfirst($reqSkill->pivot->status) }}, Skill</small><br>
									@endforeach

									@foreach($exam->corequired_skills as $coSkill)
										{{ $coSkill->name }}<br>
										<small>{{ ucfirst($coSkill->pivot->status) }}, Skill</small><br>
									@endforeach
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>

		{{-- Sidebar --}}
		@include('core::exams.sidebars.index')
	</div>
	{!! Form::close() !!}
@stop