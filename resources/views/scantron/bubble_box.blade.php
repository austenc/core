<div class="box">
	@if($includeGrid)
		<ul class="pencil-row">
			@foreach(range(0, $columns - 1) as $i)
				<li class="monospace">
					@if($data !== null && array_key_exists($i, $data))
						{{ $data[$i] }}
					@else
						&nbsp;
					@endif
				</li>
			@endforeach
		</ul>			
	@endif	
	@if( ! empty($title))
		<div class="title">{{ $title }}</div>
	@endif
	@if( ! empty($subtitle))
		<div class="subtitle">{{ $subtitle }}</div>
	@endif

	@foreach($bubbleRange as $char)
	<ul class="bubble-row {{{ $rowClass }}}">
		@foreach(range(0, $columns - 1) as $i)
			<li>
				{{-- If the column has data and the character matches, slug it  --}}
				@if(strtolower(array_get($data, $i)) == strtolower($char))
					<span class="circle filled">{{ $char }}</span>
				@else
					<span class="circle">{{ $char }}</span>
				@endif
			</li>
		@endforeach
	</ul>	
	@endforeach
</div>

