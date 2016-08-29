@extends('core::layouts.default')

@section('content')
{!! Form::model($form, ['route' => ['testforms.update', $form->id], 'method' => 'PUT']) !!}
<div class="row">
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h2>Edit Testform @include('core::testforms.partials.scrambled')</h2>	
			</div>
			{!! HTML::backlink('testforms.index') !!}
		</div>

		{{-- Warnings --}}
		@include('core::testforms.warnings.status')
		@include('core::testforms.warnings.oral')
		@include('core::testforms.warnings.spanish')

		<div class="well">
			<div class="form-group">
				{!! Form::label('name', 'Name') !!}
				{!! Form::text('name') !!}
				<span class="text-danger">{{ $errors->first('name') }}</span>
			</div>

			<div class="form-group">
				{!! Form::label('oral', 'Oral Available?') !!}<br>
				{!! Form::checkbox('is_oral', 1, $form->oral == 'Y') !!}
			</div>
		</div>
	
		<!-- Items -->
		<div class="panel panel-info">
			<div class="panel-heading">Drag and Drop to re-order testitems</div>
			<table class="table table-striped table-condensed table-hover dragdrop">
				<thead>
					<tr>
						<th></th>
						<th>Stem</th>
						<th>Answer</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@foreach($form->testitems as $item)
					<tr id="item-row-{{{ $item->id }}}">
						<td align="center">
							<span class="ordinal">{{ $item->pivot->ordinal }}</span>
							&nbsp; {!! Form::hidden('testitems[]', $item->id) !!}
						</td>
						<td class="stem">{{ $item->excerpt }}</td>
						<td class="answer">{{ $item->theAnswer->content }}</td>
						<td>
							<a href="{{ route('testitems.swap', [$item->id, $form->id]) }}" class="btn btn-sm btn-warning swap pull-right" data-toggle="modal" data-target="#swap-item">{!! Icon::retweet() !!} Swap</a>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<button class="btn btn-success btn-block" type="submit">{!! Icon::refresh() !!} Update</button>

		<a href="{{ route('testforms.scrambled', $form->id) }}" class="btn btn-block btn-info" title="Scramble Testform">
			{!! Icon::random() !!} Save As
		</a>

		@if($form->status == 'draft')
			<a class="btn btn-warning btn-block activate-btn" 
			data-confirm="Activate this testform?<br><br><p class='text-danger'>This cannot be un-done! The form can then be used in live tests.</p>">
				{!! Icon::warning_sign() !!} Activate Testform
			</a>
		@endif
	</div>
</div>
{!! Form::close() !!}

{{-- Activate testform --}}
@if($form->status == 'draft')
	{!! Form::open(['route' => ['testforms.activate', $form->id], 'class' => 'hide', 'id' => 'activateForm']) !!}
	{!! Form::close() !!}
@endif

{!! HTML::modal('swap-item') !!}
@stop

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function() {
			$('.activate-btn').click(function() {
				$('#activateForm').submit();
			});
		});
	</script>
	{!! HTML::script('vendor/jquery-sortable/source/js/jquery-sortable-min.js') !!}
	{!! HTML::script('vendor/hdmaster/core/js/testforms/ordinal.js') !!}
@stop