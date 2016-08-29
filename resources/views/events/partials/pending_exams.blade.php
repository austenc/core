<h3>Seats</h3>
<div class="well table-responsive">
	{{-- Knowledge Exams --}}
	@if( ! $event->discipline->exams->isEmpty())
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Knowledge Exam</th>
					<th>Seats</th>
				</tr>
			</thead>
			<tbody>
				{{-- Knowledge Exam Seats --}}
				@foreach($event->discipline->exams as $exam)
					<tr>
						<td class="col-md-8">
							{{ $exam->name }}
						</td>

						<td class="col-md-4">
							@if(Input::old('exam_seats.'.$exam->id))
								{!! Form::text('exam_seats['.$exam->id.'|'.$event->discipline->id.']', Input::old('exam_seats.'.$exam->id), ['class' => 'seats']) !!}
							@elseif($event->exams->find($exam->id))
								{!! Form::text('exam_seats['.$exam->id.'|'.$event->discipline->id.']', $event->exams->find($exam->id)->pivot->open_seats, ['class' => 'seats']) !!}
							@else
								{!! Form::text('exam_seats['.$exam->id.'|'.$event->discipline->id.']', '', ['class' => 'seats']) !!}	
							@endif

							{!! Form::hidden('exam_names['.$exam->id.']', $exam->name) !!}
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	@endif

	{{-- Skill Exams --}}
	@if( ! $event->discipline->skills->isEmpty())
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Skill Exam</th>
					<th>Seats</th>
				</tr>
			</thead>
			<tbody>
				{{-- Skill Exam Seats --}}
				@foreach($event->discipline->skills as $skill)
					<tr>
						<td class="col-md-8">
							{{ $skill->name }}
						</td>

						<td class="col-md-4">
							@if(Input::old('skill_seats.'.$skill->id))
								{!! Form::text('skill_seats['.$skill->id.'|'.$event->discipline->id.']', Input::old('skill_seats.'.$skill->id), ['class' => 'seats']) !!}
							@elseif($event->skills->find($skill->id))
								{!! Form::text('skill_seats['.$skill->id.'|'.$event->discipline->id.']', $event->skills->find($skill->id)->pivot->open_seats, ['class' => 'seats']) !!}	
							@else
								{!! Form::text('skill_seats['.$skill->id.'|'.$event->discipline->id.']', '', ['class' => 'seats']) !!}	
							@endif

							{!! Form::hidden('skill_names['.$skill->id.']', $skill->name) !!}
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	@endif
</div>