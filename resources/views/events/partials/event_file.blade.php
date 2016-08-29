<div class="row">
	<div class="col-sm-8">
		<h3 id="file-info">Files</h3>
	</div>

	<div class="col-sm-4">
		<div class="btn-group pull-right-sm" style="margin-top:20px;margin-bottom:10px;"> 
		    <a id="browse" class="btn btn-sm btn-info">Browse</a>
		    <a id="save" class="btn btn-sm btn-primary ">Save</a>
		    <a id="clear" class="btn btn-sm btn-warning">Clear</a>
		</div>
	</div>
</div>
<div class="well">
	@if(Auth::user()->ability(['Admin', 'Staff'], []))
		{{-- File Upload for Poweruser --}}
		<div class="form-group row">
			<div class="col-md-12">
				{!! Form::label('upload', 'Upload') !!}
				{!! Form::file('eventFiles[]', ['multiple' => 'multiple', 'style' => 'display:none;', 'id' => 'fileSelect']); !!}

				<textarea id="upload" name="upload" class="form-control"></textarea>
			</div>
		</div>

		@if( ! empty($uploadedFiles))
			<hr>
		@endif
	@endif

	{{-- Previously Uploaded Files --}}
	@if( ! empty($uploadedFiles))
		@foreach($uploadedFiles as $file)
			<a href="{{ substr($file, strpos($file, "/uploads")) }}" target="_blank">
				{{ substr($file, strrpos($file, "/") + 1) }}
			</a><br>
		@endforeach
	@else
		None
	@endif
</div>