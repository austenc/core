@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<h3 id="directions-info">Driving Directions</h3>
	<div class="well">
		<div class="row">
			<div class="col-sm-8">
				@if($facility->driving_map->originalFilename())
					<img src="{{ $facility->driving_map->url() }}" alt="Driving Map" class="img-responsive center-block">
				@endif
				<label>Upload New Map</label>
				{!! Form::file('driving_map') !!}	
			</div>
			<div class="col-sm-4">
				{!! Form::label('directions', 'Directions') !!}
				{!! Form::textarea('directions') !!}						
			</div>
		</div>
	</div>
@endif