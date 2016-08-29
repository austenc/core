<div class="answer-col">
	<div class="box">
		@foreach(range($start, $start+$max - 1) as $question)
			
			<?php 
                $id = is_array($ids) ? head(array_slice($ids, $question, 1, true)) : null;
            ?>

			@if(is_array($ids) && $id)
				<span class="{{{ $rowClass }}}-{{{ head(array_slice($ids, $question, 1, true)) }}}">
				{!! Form::hidden(
					$rowClass.'s'.'['.$id.']', 
					is_array($choices) && array_key_exists($id, $choices)
					? $choices[$id]
					: null) !!}
			@else
				<span>
			@endif

				@if(is_array($flagIds) && head(array_slice($flagIds, $question, 1, true)))
					<ul class="answer-list pencil-row {{{ $flagClass }}}">
				@else
					<ul class="answer-list pencil-row">
				@endif

					@foreach(range('A', 'E') as $letter)
						<?php $hasComment = ($letter == 'E' && is_numeric($id) && array_key_exists($id, $comments)); ?>

						{{-- Mark this as an actual answer if we passed in the answers --}}
						@if(is_array($answers) && head(array_slice($answers, $question, 1, true)) == $letter)
						<li class="answer">
						@elseif($hasComment)
						<li class="icon">
						@else
						<li>
						@endif
							{{-- Item Content --}}
							{{-- Is this letter 'E' with an id that has a comment? display the comment instead of the bubble --}}
							@if($letter == 'E' && is_numeric($id) && array_key_exists($id, $comments) && array_key_exists($id, $ordinals))
								<span class="circle glyphicon glyphicon-comment" data-toggle="popover" data-trigger="hover" data-content="{{{ $comments[$id] }}}" title="Step #{{{ $ordinals[$id] }}} - {{{ array_get($tasksByStep, $id) }}}"></span>
							@else
								{{-- if the key of the choice array exists is this question (slice) AND the value from the choice array == letter, mark it --}}
								@if(is_array($choices) && head(array_slice($ids, $question, 1, true)) && array_key_exists($id, $choices) && $choices[$id] == $letter)
									<span class="circle filled">{{ $letter }}</span>
								@else
									<span class="circle">{{ $letter }}</span>
								@endif
							@endif
						</li>
					@endforeach
				</ul>
			</span>
		@endforeach
	</div>
</div>
