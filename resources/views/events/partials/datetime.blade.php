<h3>Choose DateTime</h3>
<div class="row">
	<div class="col-md-12">
		<div class="well">
			{{-- Date / Start Time --}}
			@if(Input::old())
				@foreach (Input::old('test_date') as $i => $curr_date)
					@if ($i == 0)
						@include('core::events.partials.test_date_first', ['curr_date' => $curr_date, 'curr_time' => Input::old("start_time.{$i}"), 'i' => $i])
					@else
						@include('core::events.partials.test_date_extra', ['curr_date' => $curr_date, 'curr_time' => Input::old("start_time.{$i}"), 'i' => $i])
					@endif
				@endforeach
			@else
				@include('core::events.partials.test_date_first', ['curr_date' => NULL, 'curr_time' => NULL, 'i' => 0])
			@endif

			<hr>

			{{-- Additional Event Options --}}
			<div class="form-group">
				<div class="checkbox">
					<label>
						{!! Form::checkbox('is_regional', true, true) !!} 
						{!! Icon::globe() !!} This is a <strong>{{ strtolower(Lang::get('core::events.regional')) }}</strong> event
					</label>
				</div>
				<span class="text-danger">{{ $errors->first('is_regional') }}</span>
			</div>
			<div class="form-group">
				<div class="checkbox">
					<label>{!! Form::checkbox('is_paper', true) !!} {!! Icon::file() !!} This is a <strong>paper</strong> event</label>
				</div>
				<span class="text-danger">{{ $errors->first('is_paper') }}</span>
			</div>

		</div>
	</div>			
</div>