<div class="btn-group pull-right">	
	<a href="{{ route('steps.preview.paper', $step->id) }}" class="btn btn-link" data-hover="tooltip" title="Paper Preview" data-toggle="modal" data-target="#preview">{!! Icon::tree_conifer() !!}</a>
	<a href="{{ route('steps.preview.web', $step->id) }}" class="btn btn-link" data-hover="tooltip" title="Web Preview" data-toggle="modal" data-target="#preview">{!! Icon::facetime_video() !!}</a>

	@if(isset($addInput))
		<a href="{{ route('steps.input.add', $step->id) }}?v=task" class="btn btn-link" data-hover="tooltip" title="Add Input">{!! Icon::tag() !!}</a>
	@endif

	<a href="{{ route('steps.edit', $step->id) }}" class="btn btn-link" data-hover="tooltip" title="Edit Step">{!! Icon::pencil() !!}</a>

	@if(isset($delete))
		<a data-href="{{ route('steps.remove', $step->id) }}" data-hover="tooltip" title="Delete Step" class="btn btn-link remove-button" data-confirm="Remove <strong>(and delete)</strong> this Step?">{!! Icon::trash() !!}</a>
	@else
		<a data-hover="tooltip" title="Delete Step" class="btn btn-link remove-button" data-confirm="Remove this Step?">{!! Icon::trash() !!}</a>
	@endif
</div>