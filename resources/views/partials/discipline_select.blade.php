<h3 id="discipline-info">Disciplines</h3>
<div class="well">
	<span class="text-danger">{{ $errors->first('discipline_id') }}</span>
	<table class="table table-striped" id="discipline-table">
		<thead>
			<tr>
				@if( ! isset($item))
				<th></th>
				@endif

				<th>Name</th>
				
				@if(isset($item) && $item->getMorphClass() == 'Facility')
					<th>License</th>
				@endif

				@if(isset($item))
					@if($item->getMorphClass() == 'Facility')
						@if(in_array('Training', $item->actions))
							<th>Trainings</th>
						@endif
					@elseif( ! in_array($item->getMorphClass(), ['Observer', 'Actor', 'Proctor']))
						<th>Trainings</th>
					@endif
				@else
					<th>Trainings</th>
				@endif

				@if(isset($item))
					@if($item->getMorphClass() == 'Facility')
						@if(in_array('Testing', $item->actions))
							<th>Exams</th>
							<th>Skills</th>
						@endif
					@elseif( ! in_array($item->getMorphClass(), ['Instructor']))
						<th>Exams</th>
						<th>Skills</th>
					@endif
				@else
					<th>Exams</th>
					<th>Skills</th>
				@endif
			</tr>
		</thead>
		<tbody>
			@foreach($disciplines as $d)
				@if(Input::old('discipline_id') && in_array($d->id, Input::old('discipline_id')))
				<tr class="success" data-clickable-row>
				@else
				<tr data-clickable-row>
				@endif
					{{-- Enable/Disable Discipline (create pages) --}}
					@if( ! isset($item))
						<td>{!! Form::checkbox('discipline_id[]', $d->id) !!}</td>
					@endif

					{{-- Discipline Name --}}
					<td>
						{{ $d->name }}<br>
						<small>{{ $d->abbrev }}</small>
					</td>

					{{-- Discipline + Facility License # (for facilities, facility_discipline table) --}}
					@if(isset($item) && $item->getMorphClass() == 'Facility')
						<td class="monospace">
							{{ $d->pivot->tm_license }}
						</td>
					@endif

					{{-- Discipline Trainings --}}
					@if(isset($item))
						@if($item->getMorphClass() == 'Facility')
							@if(in_array('Training', $item->actions))
								<td>{!! implode('<br>', $d->training->lists('name')->all()) !!}</td>
							@endif
						@elseif( ! in_array($item->getMorphClass(), ['Observer', 'Actor', 'Proctor']))
							<td>{!! implode('<br>', $d->training->lists('name')->all()) !!}</td>
						@endif
					@else
						<td>{!! implode('<br>', $d->training->lists('name')->all()) !!}</td>
					@endif

					{{-- Knowledge/Skill Exams --}}
					@if(isset($item))
						@if($item->getMorphClass() == 'Facility')
							@if(in_array('Testing', $item->actions))
								{{-- Discipline Knowledge Exams --}}
								<td>{!! implode('<br>', $d->exams->lists('name')->all()) !!}</td>
								{{-- Discipline Skill Exams --}}
								<td>{!! implode('<br>', $d->skills->lists('name')->all()) !!}</td>
							@endif
						@elseif( ! in_array($item->getMorphClass(), ['Instructor']))
							{{-- Discipline Knowledge Exams --}}
							<td>{!! implode('<br>', $d->exams->lists('name')->all()) !!}</td>
							{{-- Discipline Skill Exams --}}
							<td>{!! implode('<br>', $d->skills->lists('name')->all()) !!}</td>
						@endif
					@else
						{{-- Discipline Knowledge Exams --}}
						<td>{!! implode('<br>', $d->exams->lists('name')->all()) !!}</td>
						{{-- Discipline Skill Exams --}}
						<td>{!! implode('<br>', $d->skills->lists('name')->all()) !!}</td>
					@endif
				</tr>
			@endforeach
		</tbody>
	</table>
</div>