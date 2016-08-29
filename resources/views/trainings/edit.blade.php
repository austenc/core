@extends('core::layouts.default')

@section('content')
	<div class="row">
		{!! Form::model($training, ['route' => ['trainings.update', $training->id], 'method' => 'PUT']) !!}
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>Edit Training</h1>
				</div>
				{!! HTML::backlink('trainings.index') !!}
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
						{!! Form::text('discipline_id', $training->discipline->name, ['disabled']) !!}
						{!! Form::hidden('discipline_id', $training->discipline->id) !!}
					</div>
				</div>

				<hr>

				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('valid_for', 'Valid For') !!} @include('core::partials.required')
						<small>Sets Expiration; # Months past Completion</small>
						{!! Form::text('valid_for') !!}
						<span class="text-danger">{{ $errors->first('valid_for') }}</span>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('price', 'Price') !!}
					    <div class="input-group">
					        <div class="input-group-addon">$</div>
					        {!! Form::text('price', null, ['class' => 'form-control']) !!}
					    </div>
						<span class="text-danger">{{ $errors->first('price') }}</span>
					</div>
				</div>
			</div>

			{{-- Hours --}}
			@include('core::trainings.partials.hours')

			<h3>Requirements</h3>
			<div class="well">
				<h4>Trainings</h4>
				<table class="table table-striped" id="req-training-table">
					<tbody>
						@foreach ($discipline->training as $tr)
							@if(in_array($tr->id, $training->required_trainings->lists('id')->all()))
							<tr class="warning">
							@else
							<tr>
							@endif
								<td>
									@if(in_array($tr->id, $training->required_trainings->lists('id')->all()))
									<a title="Required" data-toggle="tooltip">{!! Icon::star() !!}</a>
									@endif
									{{ $tr->name }}
								</td>
								<td>{!! Form::select('req_training_id['.$tr->id.']', [0 => 'Not Required', 1 => 'Required'], in_array($tr->id, $training->required_trainings->lists('id')->all())) !!}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			
			{{-- Timestamps --}}
			@include('core::trainings.partials.timestamps')
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
				{!! Button::success(Icon::refresh().' Update')->submit() !!}
			</div>
		</div>

		{!! Form::close() !!}
	</div>
@stop