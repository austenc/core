<table border="0" cellpadding="0" cellspacing="0" style="display:none;">
	<tr id="task-prototype">
		<td>
			@include('core::skills.tests.partials.ordinal')
		</td>
		<td>
			<span class="ordinal"></span>
			{!! Form::hidden('hidden_ids[]', null, ['class' => 'task-order']) !!}
		</td>
		<td class="weight"></td>
		<td class="title"></td>
		<td class="scenario"></td>
		<td>
			<div class="btn-group pull-right">
				<a class="btn btn-link task-link" data-toggle="tooltip" data-original-title="View Task">{!! Icon::pencil() !!}</a>
				<a class="btn btn-link remove-button" data-toggle="tooltip" data-original-title="Remove Task">{!! Icon::trash() !!}</a>
			</div>	
		</td>
	</tr>
</table>