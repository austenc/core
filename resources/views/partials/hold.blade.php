@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<div class="form-group">
		{!! Form::label('holdStatus', 'Hold') !!} <small>Stop sync with Agency</small>
		<div class="radio">
			<label>{!! Form::radio('holdStatus', false, !$holdStatus)!!} No</label>
		</div>
		<div class="radio">
			<label>{!! Form::radio('holdStatus', true, $holdStatus)!!} Yes</label>
		</div>
	</div>
@else
	<div class="form-group">
		{!! Form::label('holdStatus', 'Hold') !!} <small>Stop sync with Agency</small>
		<div class="radio">
			<label>{!! Form::radio('holdStatus', false, !$holdStatus, ['disabled'])!!} No</label>
		</div>
		<div class="radio">
			<label>{!! Form::radio('holdStatus', true, $holdStatus, ['disabled'])!!} Yes</label>
		</div>
	</div>
	{!! Form::hidden('holdStatus', $holdStatus) !!}
@endif