<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title">Import {{ Lang::choice('core::terms.facility_training', 2) }} Information</h4>
</div>
<div class="modal-body">

	<div class="alert alert-info">
		<strong>Example Datalines</strong><br>
		<small>5204|OREGON ONLINE NA PROGRAM|CHRIS PETRICK RN|1 MAIN ST||COOS BAY|OR|97420|(555) 555-1234|IMPORT@TEST.EDU|20130531|5204|U</small>
		<small>51|LANE COMMUNITY COLLEGE EUGENE|CHRIS PETRICK RN|1 W 10TH AVE||EUGENE|OR|97401|(555) 555-1234|IMPORT@TEST.EDU|20121010|51|C</small>
	</div>

	<p>[0] - License</p>
	<p>[1] - Name</p>
	<p>[2] - Administrator</p>
	<p>[3] - Address</p>
	<p>[4] - Mailing Address</p>
	<p>[5] - City</p>
	<p>[6] - State</p>
	<p>[7] - Zip</p>
	<p>[8] - Phone</p>
	<p>[9] - Email</p>
	<p>[10] - Expires</p>
	<p>[11] - License (same license as [0])</p>
	<p>[12] - Active Flag ([D]eactivate, [A]ctivate, [C]reate, [U]pdate)</p>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>