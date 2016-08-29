@if(Auth::user()->can('students.view_certs') && Config::get('core.certification.show'))
	<h3 id="certification-info">Certifications</h3>
	<div class="well table-responsive">
		<table class="table table-striped" id="certs-table">
			<thead>
				<tr>
					<th>Certification</th>
					<th>Status</th>
					<th class="hidden-xs">Certified</th>
					<th class="hidden-xs">Expires</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($allCertifications as $cert)
					@if($student->certifications && in_array($cert->id, $student->certifications->lists('id')->all()))
					<tr class="success">
					@else
					<tr>
					@endif
						<td>{{ $cert->name }}</td>
						<td>
							@if($student->certifications && in_array($cert->id, $student->certifications->lists('id')->all()))
							<span class="label label-success">
								Active
							@else
							<span class="label label-default">
								Inactive
							@endif
							</span>
						</td>
						<td>
							@if($student->certifications->find($cert->id))
								{{ date('m/d/Y', strtotime($student->certifications->find($cert->id)->pivot->certified_at)) }}
							@endif
						</td>
						<td>
							@if($student->certifications->find($cert->id))
								{{ date('m/d/Y', strtotime($student->certifications->find($cert->id)->pivot->expires_at)) }}
							@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endif