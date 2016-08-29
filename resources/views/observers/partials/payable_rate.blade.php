<h3 id="identification">Payable Rate</h3>
<div class="well">
	<div class="form-group">
		<div class="col-md-2">{!! Form::label('payable_rate', 'Rate Level') !!}</div>
		<div class="col-md-4">
			<select id="payable_rate" name="payable_rate" class="form-control">
				@foreach($payableRates as $payable)
					<option value="{{{ $payable->id }}}" 
						@if(isset($observer))
							@if($observer->payable_rate == $payable->id)
								selected='selected'
							@endif
						@endif
					> {{ $payable->level_name }}</option>
				@endforeach
			</select>
		</div>
	</div>
	<div>&nbsp;</div>
</div>