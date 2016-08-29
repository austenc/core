<div class="btn-group pull-right">
	<a class="btn btn-link" href="{{ route('steps.input.edit', [$step->id, $input->id]) }}" data-hover="tooltip" title="Edit Input">{!! Icon::pencil() !!}</a>
	<a class="btn btn-link move-input" data-href="{{{ route('steps.outcome.update', $step->id) }}}" data-hover="tooltip" title="Move to Cursor">{!! Icon::cog() !!}</a>
	<a class="btn btn-link remove-input" data-href="{{{ route('steps.input.delete', [$step->id, $input->id]) }}}" data-hover="tooltip" title="Delete Input" data-confirm="Delete this Input? Are you sure?">{!! Icon::trash() !!}</a>
</div>