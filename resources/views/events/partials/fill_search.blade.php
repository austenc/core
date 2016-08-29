				@if( ! empty (Input::get('search')))
					<div class="alert alert-info clearfix">
						Searching for "{{ Input::get('search') }}"
						<button class="btn btn-sm btn-danger pull-right" id="clear-search">Clear Search</button>
					</div>
				@endif
				<div class="form-group">
					<div class="input-group">
						{!! Form::text('search', Input::get('search'), [
							'placeholder' => 'Search for '.Lang::choice('core::terms.student', 2), 
						]) !!}

						<span class="input-group-btn">
							<button type="submit" class="btn btn-primary" id="search-btn" value="doSearch" name="searchBtn">Search</button>
						</span>
					</div>					
				</div>

@section('scripts')
	@parent
	
	<script type="text/javascript">
		$(document).ready(function(){
			// Clear the search?
			$('#clear-search').click(function() {
				$('input[name="search"]').val('');
				$('form').append($("<input>").attr('type', 'hidden').attr('name', 'searchBtn').val(true));
				
				$('#search-btn').click();
			});
		});
	</script>
@stop