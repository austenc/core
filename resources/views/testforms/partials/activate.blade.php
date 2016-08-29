@if($form->status == 'draft')
	{!! Form::open(['route' => ['testforms.activate', $form->id], 'class' => 'form-inline', 'style' => 'display: inline; margin:0; padding:0;']) !!}
		<button type="submit" class="btn btn-link" 
		data-confirm="Are you sure you want to activate this testform? <p class='text-danger'>This cannot be un-done and the form can then be used in live tests."
		data-toggle="tooltip" title="Activate">
			{!! Icon::warning_sign() !!} <span class="sr-only">Activate</span>
		</button>
	{!! Form::close() !!}
@endif
