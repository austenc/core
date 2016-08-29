<div {{ isset($id) ? 'id="'.$id.'"' : '' }} class="form-group row {{ isset($display) ? '' : 'hide' }}">

	<div class="col-md-2">
		{!! Form::label('Type') !!}
		<div class="input-type">
			{{ isset($input) ? ucfirst($input->type) : '' }}
		</div>
	</div>

	<div class="col-md-2">
		{!! Form::label('Answer') !!}
		<div class="input-answer">
			{{ isset($input) ? $input->answer : '' }}
		</div>
	</div>

	<div class="col-md-2">
		{!! Form::label('Tolerance') !!}
		<div class="input-tolerance">
			{{ isset($input) ? $input->tolerance : '' }}
		</div>
	</div>

	<div class="col-md-3">
		{!! Form::label('Options') !!}
		<div class="input-options">
			<?php
            if (isset($input)) {
                $vals = [];
                $data = explode('|', $input->value);
                foreach ($data as $i => $value) {
                    $ln = explode(',', $value);
                    if (array_key_exists(1, $ln)) {
                        $vals[] = $ln[1];
                    }
                }

                echo implode('<br>', $vals);
            }
        ?>
		</div>
	</div>

	<div class="col-md-2">
		{!! Form::label('Values') !!}
		<div class="input-extra">
		<?php
            if (isset($input)) {
                $vals = [];
                $data = explode('|', $input->value);
                foreach ($data as $i => $value) {
                    $ln = explode(',', $value);
                    $vals[] = $ln[0];
                }

                echo implode('<br>', $vals);
            }
        ?>
		</div>
	</div>

	<div class="col-md-1">
		@if(isset($toolbar))
			@include('core::skills.steps.partials.input_actions')
		@endif
	</div>

	@if(isset($input))
		{!! Form::hidden('input_id['.$c.']', $input->id, ['class' => 'input-id']) !!}
	@endif

	@if(isset($step))
		{!! Form::hidden('input_step_id['.$c.']', $step->id, ['class' => 'input-step-id']) !!}
	@endif
</div>