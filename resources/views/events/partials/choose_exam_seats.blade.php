{{-- Seats --}}
@foreach($disciplines as $disc)
	@if(Input::old('discipline_id') && Input::old('discipline_id') == $disc->id)
	<h3 class="disc disc-{{{ $disc->id }}}">
		Seats <small>Discipline {{ $disc->abbrev }}</small>
	</h3>
	<div class="well table-responsive disc disc-{{{ $disc->id }}}">
	@else
	<h3 class="disc disc-{{{ $disc->id }}}" style="display:none;">
		Seats <small>Discipline {{ $disc->abbrev }}</small>
	</h3>
	<div class="well table-responsive disc disc-{{{ $disc->id }}}" style="display:none;">
	@endif
		{{-- Knowledge Exams --}}
		@if( ! $disc->exams->isEmpty())
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Knowledge Exam</th>
						<th>Seats</th>
					</tr>
				</thead>
				<tbody>
					{{-- Knowledge Exam Seats --}}
					@foreach($disc->exams as $r)
						<tr>
							<td class="col-md-8">
								@if( ! $r->corequired_skills->isEmpty())
									<a data-toggle="tooltip" title="Corequired Skill(s): {{!! implode(', ', $r->corequired_skills->lists('name')->all()) !!}}">{!! Icon::exclamation_sign() !!}</a>
								@endif 
								{{ $r->name }}
							</td>
							<td class="col-md-4">
								@if(Input::old() && Input::old('discipline_id') == $disc->id && Input::old('exam_seats.'.$r->id))
									{!! Form::text('exam_seats['.$r->id.'|'.$disc->id.']', Input::old('exam_seats.'.$r->id), ['class' => 'seats']) !!}
								@else
									{!! Form::text('exam_seats['.$r->id.'|'.$disc->id.']', '', ['class' => 'seats']) !!}	
								@endif

								{!! Form::hidden('exam_names['.$r->id.']', $r->name) !!}
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@endif

		{{-- Skill Exams --}}
		@if( ! $disc->skills->isEmpty())
			@if( ! $disc->exams->isEmpty())
				<hr>
			@endif

			<table class="table table-striped">
				<thead>
					<tr>
						<th>Skill Exam</th>
						<th>Seats</th>
					</tr>
				</thead>
				<tbody>
					{{-- Skill Exam Seats --}}
					@foreach($disc->skills as $r)
						<tr>
							<td class="col-md-8">
								@if( ! $r->corequired_exams->isEmpty())
									<a data-toggle="tooltip" title="Corequired Exam(s): {{!! implode(', ', $r->corequired_exams->lists('name')->all()) !!}}">{!! Icon::exclamation_sign() !!}</a>
								@endif
								{{ $r->name }}
							</td>

							<td class="col-md-4">
								@if(Input::old() && Input::old('discipline_id') == $disc->id && Input::old('skill_seats.'.$r->id))
									{!! Form::text('skill_seats['.$r->id.'|'.$disc->id.']', Input::old('skill_seats.'.$r->id), ['class' => 'seats']) !!}
								@else
									{!! Form::text('skill_seats['.$r->id.'|'.$disc->id.']', '', ['class' => 'seats']) !!}
								@endif
								{!! Form::hidden('skill_names['.$r->id.']', $r->name) !!}
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@endif
	</div>
@endforeach