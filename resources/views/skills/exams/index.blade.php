@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'skillexams.index', 'method' => 'get']) !!}
	<div class="row">
		<div class="col-md-9">
			<p class="lead">Skill Exams</p>
			<div class="form-group">
				{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
				<div class="input-group">
					{!! Form::text('search', Input::get('search'), 
					['placeholder' => 'Search for Skill Exams', 'autofocus' => 'autofocus']) !!}
					<span class="input-group-btn">
						<button class="btn btn-info" type="submit">{!! Icon::search() !!} Search</button>
      				</span>
      			</div>
      		</div>

			<div class="well table-responsive">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>{!! Sorter::link('skills.index', 'Name', ['sort' => 'header']) !!}</th>
							<th>Discipline</th>
							<th>Requirements</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($skillexams as $exam)
							<tr>
								<td>
									<a href="{{ route('skillexams.edit', $exam->id) }}">
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
										<small>{{ ucfirst($reqExam->pivot->status) }}, Knowledge</small><br>
									@endforeach

									@foreach($exam->corequired_exams as $coExam)
										{{ $coExam->name }}<br>
										<small>{{ ucfirst($coExam->pivot->status) }}, Skill</small><br>
									@endforeach
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>

		{{-- Sidebar --}}
		@include('core::skills.exams.sidebars.index')
	</div>
	{!! Form::close() !!}
@stop