@foreach($instructor->disciplines as $disc)
	<h3 id="discipline-{{{ strtolower($disc->abbrev) }}}-info">
		{{ $disc->name }} <small>{{ Lang::choice('core::terms.facility_training', 2) }}</small>
	</h3>
	<div class="well">
		{{-- Training Programs under Discipline --}}
		<table class="table table-striped" id="discipline-{{ strtolower($disc->abbrev) }}-programs">
			<thead>
				<th>Name</th>
				<th>License</th>
			</thead>
			<tbody>
				<?php
                    $prFound = false;
                ?>
				@foreach($instructor->facilities as $f)
					@if($f->pivot->discipline_id == $disc->id)
						<tr>
							<td>
								<a href="{{ route('facilities.edit', $f->id) }}">{{ $f->name }}</a>
							</td>

							<td class="monospace">
								{{ $f->pivot->tm_license }}
							</td>
						</tr>
						<?php
                            $prFound = true;
                        ?>
					@endif
				@endforeach

				{{-- No Training Programs --}}
				@if( ! $prFound)
				<tr>
					<td align="center" colspan="3">
						No {{ Lang::choice('core::terms.facility_training', 2) }}
					</td>
				</tr>
				@endif
			</tbody>
		</table>
	</div>
@endforeach