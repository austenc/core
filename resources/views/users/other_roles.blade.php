@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<h3>Other Roles</h3>
	<div class="well table-responsive">			
		@if($user->roles()->count() > 1)
			<table class="table table-striped" id="other-roles">
				<tbody>
					@foreach($user->roles as $r)
						@if($ignore && $r->name != $ignore)
							<tr>
								<td>
									<span class="lead text-muted">{{ $r->name }}</span>
								</td>
								<td>
									<a class="btn-sm btn-primary pull-right" href="{{ route('person.edit', [$r->name, $user->id]) }}">
										{!! Icon::search() !!} View
									</a>
								</td>
							</tr>
						@endif
					@endforeach
				</tbody>
			</table>
		@else
			No Other Roles
		@endif
	</div>
@endif