<h3 id="discipline-info">Disciplines</h3>
<div class="well">
	<span class="text-danger">{{ $errors->first('discipline_id') }}</span>
	<table class="table table-striped" id="discipline-table">
		<thead>
			<tr>
				<th></th>
				<th>Name</th>
				<th>Trainings</th>
			</tr>
		</thead>
		<tbody>
			@foreach($disciplines as $d)
				@if(Input::old('discipline_id') && in_array($d->id, Input::old('discipline_id')))
				<tr class="success" data-clickable-row>
				@else
				<tr data-clickable-row>
				@endif
					{{-- Enable/Disable Discipline --}}
					<td>{!! Form::checkbox('discipline_id[]', $d->id) !!}</td>

					{{-- Discipline Name --}}
					<td>{{ $d->name_with_abbrev }}</td>

					{{-- Discipline Trainings --}}
					<td>{!! implode('<br>', $d->training->lists('name')->all()) !!}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</div>

@foreach($disciplines as $discipline)
	{{-- Discipline Header --}}
	@if(Input::old('discipline_id') && in_array($discipline->id, Input::old('discipline_id')))
	<h3 id="discipline-{{{ $discipline->id }}}-header" class="disc">{{ $discipline->name }}</h3>
	<div class="well disc" id="discipline-{{{ $discipline->id }}}-content">
	@else
	<h3 style="display:none;" id="discipline-{{{ $discipline->id }}}-header" class="disc">{{ $discipline->name }}</h3>
	<div style="display:none;" class="well disc" id="discipline-{{{ $discipline->id }}}-content">
	@endif
		{{-- Trainings --}}
		<h4>Trainings</h4>
		<span class="text-danger">{{ $errors->first('training_id') }}</span>
		<table class="table table-striped">
			<thead>
				<tr>
					<th class="span1"></th>
					<th>Name</th>
				</tr>
			</thead>
			<tbody>
				@foreach($discipline->training as $i => $training)
					@if(Input::old('training_id') && in_array($training->id, Input::old('training_id')))
					<tr class="success" data-clickable-row>
					@else
					<tr data-clickable-row>
					@endif
						<td>{!! Form::checkbox('training_id[]', $training->id) !!}</td>
						<td>{{ $training->name_with_abbrev }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>

		<hr>

		{{-- Training Programs --}}
		<h4>{{ Lang::choice('core::terms.facility_training', 2) }}</h4>
		<span class="text-danger">{{ $errors->first('training_site_id') }}</span>
		<table class="table table-striped">
			<thead>
				<tr>
					<th class="span1"></th>
					<th>Name</th>
				</tr>
			</thead>
			<tbody>
				@foreach($discipline->trainingPrograms as $i => $site)
					@if(Input::old('training_site_id') && in_array($discipline->id.'|'.$site->id, Input::old('training_site_id')))
					<tr class="success" data-clickable-row>
					@else
					<tr data-clickable-row>
					@endif
						<td>{!! Form::checkbox('training_site_id[]', $discipline->id.'|'.$site->id) !!}</td>
						<td>
							{{ $site->name }}<br>
							<small>#{{ $site->pivot->tm_license }}</small>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endforeach