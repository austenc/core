@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => ['facilities.person.store', $facility->id]]) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Add Person <small>{{ $facility->name }}</small></h1>
			</div>
			<div class="col-xs-4 back-link">
				<a href="{{ route('facilities.edit', $facility->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back</a>
			</div>
		</div>

		<div class="well">
			{{-- Select Discipline --}}
			<div class="form-group">
				{!! Form::label('discipline_id', 'Discipline') !!}
				{!! Form::select('discipline_id', [0 => 'Select Discipline'] + $disciplines, $selDiscipline) !!}
			</div>

			{{-- Person Type --}}
			<div class="form-group">
				{!! Form::label('person_type', 'Person Type') !!}
				{!! Form::select('person_type', [0 => 'Select Person Type'] + $personTypes, $selPersonType) !!}
			</div>
		</div>

		{{-- Available People --}}
		<div class="well">
			<div class="form-group">
				<table class="table table-striped" id="person-table">
					<thead>
						<tr>
							<th></th>
							<th>Name</th>
							<th>Location</th>
						</tr>
					</thead>
					<tbody>
						@if( ! empty($avPeople))
							@foreach($avPeople as $person)
								<tr>
									<td>{!! Form::checkbox('person_id[]', $person->id) !!}</td>
									<td>{{ $person->first }} {{ $person->last }}</td>
									<td>{{ $person->city }}, {{ $person->state }}</td>
								</tr>
							@endforeach
						@endif
					</tbody>
				</table>
			</div>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			{!! Button::success(Icon::plus_sign().' Add')->submit() !!}
		</div>
	</div>

	{!! Form::input('hidden', 'facility_id', $facility->id, ['id' => 'facility_id']) !!}
	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/facilities/add_person.js') !!}
@stop