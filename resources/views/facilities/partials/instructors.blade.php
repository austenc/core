<h4>{{ Lang::choice('core::terms.instructor', 2) }}</h4>
<table class="table table-striped" id="disc-{{ strtolower($discipline->abbrev) }}-instructor-table">
	<thead>
		<tr>
			<th>Name</th>
			<th>TM License</th>
			<th>Type</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		@include('core::facilities.partials.people_row', ['items' => $facility->instructors, 'type' => 'instructor'])
	</tbody>
</table>