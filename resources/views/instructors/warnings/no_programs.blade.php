@if(Auth::user()->ability(['Admin', 'Staff'], []))
	@if($instructor->activeFacilities()->get()->isEmpty())
		<div class="alert alert-warning">
			{!! Icon::flag() !!} <strong>No {{ Lang::choice('core::terms.facility_training', 2) }}</strong> Login unavailable until active {{ Lang::choice('core::terms.facility_training', 2) }} associated
		</div>
	@endif
@endif