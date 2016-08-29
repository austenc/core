<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title">Import {{ Lang::choice('core::terms.instructor', 2) }} Information</h4>
</div>
<div class="modal-body">

	<div class="alert alert-info">
		<strong>Example Dataline</strong><br>
		<small>3421|Avamere Health|000000000RN|PETRICK|CHRIS|1 Main St||PORTLAND|OR|97222|||D|2013-03-13 00:00:00.000</small>
	</div>

	<p>[0] - Training Program License</p>
	<p>[1] - Training Program Name</p>
	<p>[2] - RN License</p>
	<p>[3] - Last Name</p>
	<p>[4] - First Name</p>
	<p>[5] - Address</p>
	<p>[6] - Mailing Address</p>
	<p>[7] - City</p>
	<p>[8] - State</p>
	<p>[9] - Zip</p>
	<p>[10] - ???</p>
	<p>[11] - Email</p>
	<p>[12] - Active Flag ([D]eactivate, [A]ctivate, [R]eactivate)</p>
	<p>[13] - Expires</p>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>