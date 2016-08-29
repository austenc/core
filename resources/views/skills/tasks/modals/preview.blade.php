<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title">Step #{{ $step->ordinal }} - Preview {{ ucfirst($version) }}</h4>
</div>
<div class="modal-body">
	<div class="well">
		{{ $outcome }}
	</div>
</div>
<div class="modal-footer">
	<a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
</div>