@extends('core::layouts.default')

@section('content')
	<div class="row">
		<div class="col-xs-8">
			<h3>{{ $form->name }} @include('core::testforms.partials.scrambled')</h3>
		</div>
		{!! HTML::backlink('testforms.index') !!}
	</div>

	{{-- Warnings --}}
	@include('core::testforms.warnings.status')
	@include('core::testforms.warnings.oral')

	<div class="well">
		<table class="table table-striped table-hover dragdrop">
			<thead>
				<tr>
					<th>#</th>
					<th>Stem</th>
					<th>Answer Letter</th>
					<th>Answer</th>
				</tr>
			</thead>
			<tbody>
				@foreach($form->testitems as $item)
				<tr>
					<td>
						<span class="ordinal lead text-muted">{{ $item->pivot->ordinal }}</span>
					</td>

					<td>
						<a href="{{ route('testitems.show', $item->id) }}">
							{{ $item->excerpt }}
						</a>
					</td>
					<td>{{{ $item->theAnswer->letter }}}</td>
					<td>{{ $item->theAnswer->content }}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@stop