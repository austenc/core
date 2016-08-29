<table class="box">

	{{-- Write-in boxes --}}
	@if($includeGrid)
		<tr class="pencil-row">
			@foreach(range(0, $columns - 1) as $i)
				<td class="monospace">
					@if($data !== null && array_key_exists($i, $data))
						{{ $data[$i] }}
					@else
						&nbsp;
					@endif
				</td>
			@endforeach
		</tr>			
	@endif	

	{{-- Title and Subtitle --}}
	@if( ! empty($title))
		<tr>
			<td class="title" colspan="{{{ $columns }}}">{{ $title }}</td>
		</tr>
	@endif
	@if( ! empty($subtitle))
		<tr>
			<td class="subtitle" colspan="{{{ $columns }}}">{{ $subtitle }}</td>
		</tr>
	@endif

	{{-- Bubbles --}}
	@foreach($bubbleRange as $char)
	<tr class="bubble-row {{{ $rowClass }}}">
		@foreach(range(0, $columns - 1) as $i)
			<td>
				{{-- If the column has data and the character matches, slug it  --}}
				@if(strtolower(array_get($data, $i)) == strtolower($char))
					<span class="circle filled">{{ $char }}</span>
				@else
					<span class="circle">{{ $char }}</span>
				@endif
			</td>
		@endforeach
	</tr>	
	@endforeach

</table>
