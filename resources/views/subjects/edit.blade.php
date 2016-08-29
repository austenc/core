@extends('core::layouts.default')

@section('content')
	{!! Form::model($subject, ['route' => ['subjects.update', $subject->id], 'method' => 'PUT']) !!}
	<div class="row">
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>Edit Subject</h1>
				</div>
				{!! HTML::backlink('subjects.index') !!}
			</div>

			{{-- Warnings --}}
			@if($subject->report_as)
			<div class="alert alert-warning">
				{!! Icon::flag() !!} <strong>Report As</strong> Subject is reporting under a different subject
			</div>
			@endif

			<h3>Basic Info</h3>
			<div class="well">
				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('exam_id', 'Exam') !!}
						{!! Form::select('exam_id', [$subject->exam->id => $subject->exam->name], true, ['disabled']) !!}
						{!! Form::hidden('exam_id', $subject->exam->id) !!}
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('name', 'Name') !!} @include('core::partials.required')
						{!! Form::text('name') !!}
						<span class="text-danger">{{ $errors->first('name') }}</span>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('old_number', 'Old Subject #') !!}
						{!! Form::text('old_number') !!}
						<span class="text-danger">{{ $errors->first('old_number') }}</span>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('report_as', 'Report As') !!}
						{!! Form::select('report_as', [0 => 'Self'] + $reportAsOpts, $subject->report_as) !!}
					</div>
				</div>
			</div>

			<h3>Testitems</h3>
			<div class="well">
				<table class="table table-striped">
	      			<thead>
	      				<th>#</th>
	      				<th>Stem</th>
	      				<th>Status</th>
	      			</thead>
	      			<tbody>
						@foreach($subject->testitems as $item)
							@if($item->status == 'active')
							<tr class="success">
							@else
							<tr class="warning">
							@endif
								<td><span class="lead text-muted">{{ $item->id }}</span></td>
								<td><a href="{{ route('testitems.edit', $item->id) }}">{{ $item->stem }}</a></td>
								<td>
									@if($item->status == 'active')
										<span class="label label-success">{{ ucfirst($item->status) }}</span>
									@else
										<span class="label label-warning">{{ ucfirst($item->status) }}</span>
									@endif
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>

		{{-- Sidebar  --}}
		<div class="col-md-3 sidebar">
			<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
				{!! Button::success(Icon::refresh().' Update')->submit() !!}
			</div>
		</div>
	</div>
	{!! Form::close() !!}
@stop