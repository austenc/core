@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<div class="form-group">
		{!! Form::label('lockStatus', 'Locked') !!} <small>Prevent Login</small>
		<div class="radio">
			<label>{!! Form::radio('lockStatus', false, !$lockStatus) !!} No</label>
		</div>
		<div class="radio">
			<label>{!! Form::radio('lockStatus', true, $lockStatus) !!} Yes</label>
		</div>
	</div>
@else
	<div class="form-group">
		{!! Form::label('lockStatus', 'Locked') !!} <small>Prevent Login</small>
		<div class="radio">
			<label>{!! Form::radio('lockStatus', false, !$lockStatus, ['disabled']) !!} No</label>
		</div>
		<div class="radio">
			<label>{!! Form::radio('lockStatus', true, $lockStatus, ['disabled']) !!} Yes</label>
		</div>
	</div>
	{!! Form::hidden('lockStatus', $lockStatus) !!}
@endif