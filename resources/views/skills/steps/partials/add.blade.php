<table border="0" cellpadding="0" cellspacing="0" style="display:none;">
	<tr class="step" id="step-prototype">
		<td>
			@include('core::skills.steps.partials.ordinal')
		</td>
		<td>
			<span class="ordinal"></span>
		</td>
		<td>
			<input name="step_key[]" type="checkbox" value="1" class="step-key">
			<input name="step_ids[]" type="hidden" value="-1" class="step-ids">
		</td>
		<td class="col-md-1">
			<input name="step_weights[]" class="form-control step-weight" value="">
		</td>
		<td>
			<textarea name="step_outcomes[]" class="form-control step-outcomes"></textarea>
			<input name="step_order[]" type="hidden" class="step-order" value="99">
		</td>
		<td>
			<textarea name="step_comments[]" class="form-control step-comments"></textarea>
			<input name="step_alts[]" type="hidden" class="step-alts">
		</td>
		<td>
			<button class="btn btn-link remove-button pull-right">{!! Icon::trash() !!}</button>
		</td>
	</tr>	
</table>