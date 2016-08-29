<div class="test-confirmation-page">
	<h4 class="text-center">HEADMASTER Test Confirmation - {{ Config::get('core.client.name') }} {{ $exam or '' }}</h4>
	<div class="well">
		<div class="letter-header">
			<div class="row">
				<div class="btn-group pull-right">
					<a href="{{ route('facilities.directions', $f->id) }}" class="btn btn-success btn-directions">
						{!! Icon::road() !!} Get Directions
					</a>
					<a href="javascript:window.print();" class="btn btn-primary">
						<span class="glyphicon glyphicon-print"></span> Print
					</a>
				</div>
			</div>
			<div class="row pad-btm">
				<div class="col-xs-2">
					<strong>Test Date:</strong>
				</div>
				<div class="col-xs-10">
					{{ $event->test_date }}
				</div>
			</div>
			<div class="row">
				<div class="col-xs-2">
					<strong>Test Site:</strong>
				</div>
				<div class="col-xs-10">
					{{ $f->name }} <br>
					{{ $f->address }} <br>
					{{ $f->city }}, {{ $f->state }} {{ $f->zip }} <br>
				</div>
			</div>
			
			<div class="row no-btm-margin">
				<div class="col-xs-12">
					<div class="to-address">
						<strong>
							{{ $s->fullName }} <br>
							{{ $s->address }} <br>
							{{ $s->city }}, {{ $s->state }} {{ $s->zip }} <br>
						</strong>
					</div>
				</div>
			</div>
		</div>
		
		<hr class="hidden-print">

		<div class="row">
			<small>
				<ul>
					<li class="text-yell">Testing begins at {{ $event->start_time }} arrive 20 minutes early to check-in</li>
					<li>You must <span class="text-yell">bring two forms of identification</span></li>
					<li>One ID must be a SIGNED, CURRENT (not expired), DATE BEARING GOVERNMENT-ISSUED PHOTO ID CARD (drivers license, passport, military ID, etc...) <br> The second ID may be a SIGNED Social Security card or other identification that is SIGNED, CURRENT (not expired) and DATE-BEARING (credit/debit cards, First Aid/CPR cards, fishing/hunting licenses, signed school ID showing school year or semester, etc...</li>
					<li><span class="text-yell">If you are late or do not bring two id's, you will not be allowed to test and you will be considered a "no-show"</span>. No shows must pay the reschedule fee</li>
					<li>Bring several #2 pencils with erasers. DON'T USE INK PENS.</li>
					<li>IF YOU CANNOT TEST FOR ANY REASON NOTIFY HEADMASTER IMMEDIATELY</li>
					<li class="text-yell">Reschedule at least 3 business days prior to your test date</li>
					<li>You may reschedule online by logging into your account. If you are unable to re-schedule online, call Headmaster at 800-393-8664 for assistance.</li>
					<li>ADA accommodation requests must be submitted with your applicate and approved prior to testing</li>
					<li>You may not test if you have any type of temporary physical limitation that would prevent you from performing duties as a CNA (casts, crutches, etc.) or if you have a contagious illness</li>
					<li>If you have been on "Light Duty" at work you will not be allowed to test without a Doctor's Release</li>
					<li>If weather presents a safety issue, contact Headmaster immediately. Leave a message if after hours</li>
					<li>FAMILY MEMBERS, FRIENDS AND PETS ARE NOT PERMITTED IN THE TESTING AREA</li>
					<li>Fees are non-refundable. Test cancellation requests require at least 3 business days notice</li>
				</ul>
			</small>
		</div>
	
		@if($f->driving_map->originalFilename() || ! empty($f->directions))
			<hr class="hidden-print">
			<div class="row">
				<div class="col-xs-7">
					<img src="{{ $f->driving_map->url('medium') }}" alt="Driving Directions" class="img-responsive">
				</div>
				<div class="col-xs-5">
					<strong class="center-block">Driving Directions</strong>
					{{ $f->directions }}
				</div>
			</div>
		@endif
	</div>
</div>