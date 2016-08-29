<div class="form-group">
	{!! Form::label('agency_only', 'Agency Only') !!} <small>Special Agency Program</small>
	<div class="radio">
		<label>{!! Form::radio('agency_only', false, !$agencyOnly) !!} No</label>
	</div>
	<div class="radio">
		<label>{!! Form::radio('agency_only', true, $agencyOnly) !!} Yes</label>
	</div>
</div>