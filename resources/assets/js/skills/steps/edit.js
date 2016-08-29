var enemy_btn_href;

$(document).ready(function(){

    $(function () { $("[data-hover='tooltip']").tooltip({container: 'body'}); });

    autosize($('textarea'));

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
        var $row    = $(this).closest('tr');
        var inputId = $row.find('.input-id').val();
        var inputBB = '[input id="'+inputId+'"]';
        var $outcome = $('#expected_outcome');

        $.ajax({
            url: $(this).data('href'),
            success: function(result){                    
                $('#dataConfirmModal').modal('hide');

                // remove input tag from step outcome
                var replaced = $outcome.val().replace(inputBB, '');
                $outcome.val(replaced);

                // display message
                flash('Removed Input #'+inputId, 'success');
            }
        });
    });

    // move input
    $(document).on('click', '.move-input', function(e){
        e.preventDefault();

		var href     = $(this).data('href');
		var $row     = $(this).closest('tr');
		var inputId  = $row.find('.input-id').val();
		var inputTag = '[input id="'+inputId+'"]';

		// adjust input tag
	    insertAtCaret($('#expected_outcome'), inputTag);

        // save modified step outcome
        if(href)
        {
            $.ajax({
                url: href,
                data: {'expected_outcome' : $('#expected_outcome').val()},
                success: function(result){

                    if(result.message)
                    {                
                        flash(result.message, 'success');
                    }
                }
            });
        }
    });

    // update input (dropdown/textbox/radio)
    $(document).on('click', '#update-input-btn', function(e){
        e.preventDefault();

        $form = $(this).closest('form');

        // get form fields here
        var type = $form.find('.new-input-type option:selected').text();
        var answer = $form.find('.new-input-answer').val();
        var tolerance = $form.find('.new-input-tolerance').val();
        var extra = $form.find('.new-input-value').val();

        var step_id = $form.find('.step-id').val();
        var input_id = $form.find('.input-id').val();

        // find cloned row in the table
        var $inputRow = $('#steps').find('.input-'+input_id);

        // update all step input rows
        $inputRow.find('.input-type').html(type);
        $inputRow.find('.input-answer').html(answer);
        $inputRow.find('.input-tolerance').html(tolerance);
        $inputRow.find('.input-extra').html(extra);

        // update in db
        $.ajax({
            type: "POST",
            url: $form.attr('action'),
            data: $form.serialize(),
            success: function(result)
            {
                if(result.message)
                {
                    flash(result.message, 'success');
                }
            }
        });
    });

    // add input (dropdown/textbox/radio)
    $(document).on('click', '#save-input-btn', function(e){
        e.preventDefault();

        $form = $(this).closest('form');

        // get form fields here
        var type      = $form.find('.new-input-type option:selected').text();
        var answer    = $form.find('.new-input-answer').val();
        var tolerance = $form.find('.new-input-tolerance').val();
        var extra     = $form.find('.new-input-value').val();
        var step_id   = $form.find('.step-id').val();

        // clone partial and add to step
        var clonedInput = $('#input-prototype').clone();
        clonedInput.removeAttr('id');
        clonedInput.removeClass('hide');
        clonedInput.find('.input-type').html(type);
        clonedInput.find('.input-name').html(name);
        clonedInput.find('.input-answer').html(answer);
        clonedInput.find('.input-tolerance').html(tolerance);
        clonedInput.find('.input-extra').html(extra);
        clonedInput = clonedInput.wrap($('<td colspan="7"></td>')).parent();
        clonedInput = clonedInput.wrap($('<tr class="input"></tr>')).parent();
        clonedInput.show();

        $.ajax({
            type: "POST",
            url: $form.attr('action'),
            data: $form.serialize(),
            success: function(result)
            {
                if(result.message)
                {
                    flash(result.message, 'success');
                }

                clonedInput.find('tr.input').addClass('input-'+result.input_id);
                clonedInput.find('.input-text-id').html(result.input_id);

                // find the step row in the table (and append [input] to outcome)
                var $currRow = $('#steps').find('.step-'+step_id);
                var $targetArea = $currRow.find('.step-outcomes');
                var targetText = $targetArea.val(); 
                $targetArea.val(targetText+'[input id="'+result.input_id+'"]');
                
                // find the next step row after $currRow
                var $lastInput = $currRow.nextUntil('.step').last();

                if($lastInput.length == 0)
                {
                    $currRow.after(clonedInput);
                }
                else
                {
                   $lastInput.after(clonedInput);
                }

            }
        });
        
        $(function () { $("[data-hover='tooltip']").tooltip({container: 'body'}); });
    });

});