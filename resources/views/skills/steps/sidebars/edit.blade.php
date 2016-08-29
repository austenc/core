<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	<button type="submit" class="btn btn-success">{!! Icon::refresh() !!} Update</button>

	@if($step->vinput_review)	
		<a href="{{ route('steps.unflag', $step->id) }}" class="btn btn-warning" data-confirm="Remove variable input flag warning? This item should be double-checked for any possible missing input.<br><br>Are you sure?">
			{!! Icon::flag() !!} Unflag
		</a>
	@endif

	<a href="{{ route('steps.input.add', $step->id) }}" class="add-setup btn btn-default">{!! Icon::tags() !!} Add Input</a>
	<a href="{{ route('steps.preview.paper', $step->id) }}" class="btn btn-default" data-toggle="modal" data-target="#preview">{!! Icon::tree_conifer() !!} Paper Preview</a>
	<a href="{{ route('steps.preview.web', $step->id) }}" class="btn btn-default" data-toggle="modal" data-target="#preview">{!! Icon::facetime_video() !!} Web Preview</a>
</div>