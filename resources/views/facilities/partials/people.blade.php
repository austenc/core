<div class="row">
	<div class="col-xs-8">
		<h3 id="facility-{{{ strtolower($discipline->abbrev) }}}-title">
			{{ $discipline->name }} 
			@if( ! $discipline->pivot->active)
				<small>(disabled)</small>
			@endif
		</h3>
	</div>
	<div class="col-xs-4 valign-heading">
		@if($discipline->pivot->active && ! Form::isDisabled() && Auth::user()->can('facilities.remove.discipline'))
			<a href="{{ route('facilities.discipline.deactivate', [$facility->id, $discipline->id]) }}" id="remove-disc-{{{ $discipline->id }}}" class="btn btn-sm btn-danger pull-right" data-confirm="Remove {{{ $discipline->name }}} for this {{{ Lang::choice('core::terms.facility', 1) }}}?<br><br>Are you sure?">
				{!! Icon::exclamation_sign() !!} Deactivate
			</a>
		@endif

		{{-- Add Affiliates --}}
		@if( ! Form::isDisabled() && Auth::user()->can('facilities.manage_affiliated'))
			<a href="{{ route('facilities.affiliate.attach', [$facility->id, $discipline->id]) }}" data-toggle="modal" data-target="#add-affiliate" class="btn btn-default btn-sm pull-right" id="attach-affiliate-{{ strtolower($discipline->abbrev) }}-btn">
				{!! Icon::plus_sign() !!} Add Affiliate
			</a>
		@endif
	</div>
</div>
<div class="well">
	@include('core::facilities.partials.discipline_info', ['discipline' => $discipline])

	@include('core::facilities.partials.instructors', ['discipline' => $discipline])

	@include('core::facilities.partials.test_team', ['discipline' => $discipline])

	@include('core::facilities.partials.testing_affiliated', ['discipline' => $discipline, 'affiliates' => $affiliates[$discipline->id]])
</div>