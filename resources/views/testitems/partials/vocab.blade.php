@section('scripts')

@parent 

	{!! HTML::script('vendor/bootstrap3-typeahead/bootstrap3-typeahead.min.js') !!}
	{!! HTML::script('vendor/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js') !!}

	<script>
		// Setup tagsinput and typeahead to play nice
		$('#vocab').tagsinput({
			typeahead: {                  
				source: function(query) {
				  return $('#vocab').data('source');
				}
			}
		});

		// Mimic the enter press for tagsinput via + button
		$('.add-vocab').click(function(e){
			e.preventDefault();
			$vocab = $('#vocab');
			$input = $vocab.tagsinput('input');
			tag = $input.val();
			$vocab.tagsinput('add', tag);
			$input.val('');
			$input.focus();
		});
	</script>
@stop