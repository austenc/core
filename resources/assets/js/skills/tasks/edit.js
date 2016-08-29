var enemy_btn_href;

$(document).ready(function(){

    autosize($('textarea').not('#step-prototype textarea').not('#setup-prototype textarea'));

     // adjust add enemy btn url
     enemy_btn_href = $('.find-enemies').prop('href');
     adjust_add_enemy_url();

     $(function () { $("[data-hover='tooltip']").tooltip({container: 'body'}); });

	// add step
	$('body').on('click', '.add-step', function(){
		var table    = $('#steps');
		var numSteps = $('#steps tbody tr.step').length;
		var newRow   = $('#step-prototype').clone();

		// Remove the cloned table id preventing issues of having duplicate id's in the dom.
		newRow.removeAttr('id');

		// set ordinal and other values 
		newRow.find('.ordinal').html(numSteps + 1);							// Sets the ordinal number for display
		newRow.find('.step-key').attr('value', numSteps);						// Sets the key of the task
		newRow.find('.step-order').attr('value', numSteps + 1);					// Sets the order of the task

		// Set names of fields using an array value on insert of clone
		// This step is required for Laravel and Input::old() as array field names without an assigned
		// index cause htmlentity errors when trying ot access this data.
		newRow.find('.step-key').attr('name', 'step_key[' + numSteps + ']');
		newRow.find('.step-ids').attr('name', 'step_ids[' + numSteps + ']');
		newRow.find('.step-weight').attr('name', 'step_weights[' + numSteps + ']');
		newRow.find('.step-outcomes').attr('name', 'step_outcomes[' + numSteps + ']');
		newRow.find('.step-order').attr('name', 'step_order[' + numSteps + ']');
		newRow.find('.step-comments').attr('name', 'step_comments[' + numSteps + ']');
		newRow.find('.step-alts').attr('name', 'step_alts[' + numSteps + ']');

		update_ordinals(newRow);

		// Insert the new table row into the dom.
		table.append(newRow);
        
		autosize($('textarea', table));

		$(function () { 
			$("[data-hover='tooltip']").tooltip({container: 'body'}); 
		});
	});
    
	// remove step
	$('#steps').on('click', '.remove-button', function(e){
        e.preventDefault();

        var hasConfirm = $(this).attr('data-confirm');

        if(hasConfirm != undefined)
            return true;

        var href = $(this).data('href');

        if(href)
        {
            // Do the ajax remove
            var $row = $(this).parents('tr');

            $.ajax({
                url: href,
                success: function(result){                    
                    $('#dataConfirmModal').modal('hide');
                    fadeAndReorder($row, 'Step removed.', 'danger');                    
                }
            });
        }
        else
        {
            // just remove the row
            var $row = $(this).parents('tr');
            fadeAndReorder($row, 'Step removed.', 'danger');
        }
	});

	// preview paper test
    $(document).on('click', '.paper-preview-step', function(e){
        e.preventDefault();

        var theVal = $(this).parents('tr').find('textarea:first').val();
        var paperPreview = '<pre class="paper-preview">'+parseBBPaper(theVal, '')+"</pre>";
        $('#preview').find('.modal-body').html(paperPreview);
    });
    // preview web test
    $(document).on('click', '.web-preview-step', function(e){
        e.preventDefault();
        
        var theVal = $(this).parents('tr').find('textarea:first').val();
        var webPreview = '<pre class="web-preview">'+parseBBWeb(theVal, '')+"</pre>";
        $('#preview').find('.modal-body').html(webPreview);
    });

    // remove input
    $(document).on('click', '.remove-input', function(e){
        e.preventDefault();

        var hasConfirm = $(this).attr('data-confirm');

        if(hasConfirm != undefined)
            return true;

        // input row
        var $inputRow = $(this).closest('tr');
        var inputId = $inputRow.find('.input-id').val();
        var inputBB = '[input id="'+inputId+'"]';
        // step row
        var stepId = $inputRow.find('.input-step-id').val();
        var $stepRow = $('#steps').find('.step-'+stepId);
        var $stepOutcome = $stepRow.find('.step-outcomes');

        $.ajax({
            url: $(this).data('href'),
            success: function(result){                    
                $('#dataConfirmModal').modal('hide');

                // remove input tag from step outcome
                var replaceOutcome = $stepOutcome.val().replace(inputBB, '');
                $stepRow.find('.step-outcomes').val(replaceOutcome);

                // remove input row
                $inputRow.fadeOut(400, function() { 
                    $inputRow.remove();
                });

                // display message
                if(result.message)
                {
                    flash(result.message, 'success');  
                }

                // animate success on step row
            }
        });
    });

    // move input
    $(document).on('click', '.move-input', function(e){
        e.preventDefault();

        var href     = $(this).data('href');
        var $row     = $(this).closest('tr');
        var inputId  = $row.find('.input-id').val();
        var stepId   = $row.find('.input-step-id').val();
        var inputTag = '[input id="'+inputId+'"]';

        // find corresponding step row
        var $stepRow = $('#steps tbody').find('.step-'+stepId);
        var $outcome = $stepRow.find('.step-outcomes');
        
        insertAtCaret($outcome, inputTag);

        // save modified step outcome
        if(href)
        {
            $.ajax({
                url: href,
                data: {'expected_outcome' : $outcome.val()},
                success: function(result){

                    if(result.message)
                    {                
                        flash(result.message, 'success');
                    }
                }
            });
        }
    });

	// add setup
	$(document).on('click', '.add-setup', function(){
		var table      = $('#setups');
		var numSetups = $('#setups tbody tr').length;
		var newRow     = $('#setup-prototype').clone();
		newRow.removeAttr('id');

		newRow.find('.setup-order').html(numSetups + 1);
		newRow.find('.setup-id').attr('name', 'setup_ids[' + numSetups + ']');
		newRow.find('.setups').attr('name', 'setups[' + numSetups + ']');
		newRow.find('.setup-comments').attr('name', 'setup_comments[' + numSetups + ']');
		table.append(newRow);

		autosize($('textarea', table));
	});

    // remove setup
    $('#setups').on('click', '.remove-button', function(e){
        e.preventDefault();

        var hasConfirm = $(this).attr('data-confirm');

        if(hasConfirm != undefined)
            return true;

        var href = $(this).data('href');
        var $row = $(this).parents('tr');

        if(href)
        {
            // Do the ajax remove
            $.ajax({
                url: href,
                success: function(result){                    
                    $('#dataConfirmModal').modal('hide');
                    $row.remove();           
                    flash('Setup removed.', 'danger');

                    $('#setups tr').has('.setup-order').each(function(i){
                        $(this).find('.setup-order').html(i+1);
                    });
                }
            });
        }
        else
        {
            $row.remove();           
            flash('Setup removed.', 'danger');
        }
    });


    // SORT up button
    $(document).on("click",".sort-up",function(e){
        e.preventDefault();
        
        // current row
        var tr = $(this).parents('tr');
        // get all input rows for this step
        var inputs = tr.nextUntil('.step');

        // move up (nothing above header-row)
        if(tr.prev().attr('class') != "header-row")
        {
            // move current row above the previous step
            tr.prevAll('tr.step:first').before(tr);

            // get next step (after current row has moved)
            var nextStep = tr.nextAll('tr.step:first');

            // move all inputs associated with step
            $.each(inputs, function(){
                nextStep.before(this); 
            });
            
            // update ordinals
            setTimeout(function() {
                update_ordinals(tr);
            }, 300);
        }
    });
    
    // SORT down button
    $(document).on("click",".sort-down",function(e){
        e.preventDefault();
        
        // current row
        var tr = $(this).parents('tr');
        // get all input rows for this step
        var inputs = tr.nextUntil('.step');
        // move current row below the next step (and after any inputs)
        var nextStep = tr.nextAll('tr.step:first');
        // does this step have inputs?
        var nextStepInputs = nextStep.nextUntil('.step');

        // next step has inputs...
        if(nextStepInputs.length > 0)
        {
            // insert after the last input
            nextStepInputs.last().after(tr);
        }
        else
        {
            nextStep.after(tr);
        }

        // move the inputs
        $.each(inputs, function(){
            tr.after(this);
        });
        
        // update ordinals
        setTimeout(function() {
            update_ordinals(tr);
        }, 300);
    });

    // add enemies 
    $(document).on('click', '#add-enemies-btn', function(e){
        var enemy_ids = [];
        var prev_ids = $('#enemies').val();

        // previous enemies
        if(prev_ids.length > 0)
        {
            enemy_ids = prev_ids.replace(/ /g,'').split(',');
        }

        // get selected enemy ids, add to enemy array
        $('#enemies-table tbody input:checkbox:checked').each(function(){
            enemy_ids.push($(this).val());
        });        

        $('#enemies').val(enemy_ids.join(', '));

        // change url of add enemies to exclude these
        adjust_add_enemy_url();
    });
    
}); // document.ready

function update_ordinals(row)
{
    var j = 1;

    if(row.hasClass('step'))
    {
        $('#steps tr').has('.step-order').each(function(i){
            $(this).find('.step-order').val(j);    // hidden ordinal #
            $(this).find('.ordinal').html(j);        // display #   
            
            // update step_key index
            $(this).find('.step-key').prop('name', 'step_key['+(j - 1)+']');
            j++;
        });
    }
}
function fadeAndReorder(row, message, msg_type)
{
    row.fadeOut(400, function() { 
        // remove step input rows
        var $inputs = row.nextUntil('.step');
        $.each($inputs, function(i, val){
            $(this).remove();
        });
        
        // remove step row
        row.remove();
        
        // update row numbers
        update_ordinals(row);

        if(message)
        {
            flash(message, msg_type);
        }
    });
}
function adjust_add_enemy_url()
{
    var enemy_ids = $('#enemies').val().replace(/ /g,'');
    $(".find-enemies").prop('href', enemy_btn_href+"?exclude="+enemy_ids);
}