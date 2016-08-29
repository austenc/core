@if(empty($event->test_date))
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>Test Team</strong> Disabled until Test Date is set
	</div>
@else
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>Test Team</strong> Will be reset if you change Test Date
	</div>
@endif