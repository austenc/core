$(document).ready(function(){

    // Force page reload on back button
    var d = new Date();
    d = d.getTime();
    if ($('#reloadValue').val().length == 0)
    {
            $('#reloadValue').val(d);
            $('body').show();
    }
    else
    {
            $('#reloadValue').val('');
            location.reload();
    }

    // Disable the 'I want to end this test' button until checkbox checked
    $('#end-test-confirm').prop('disabled', true);
    $('body').on('click', '#agree_box', function(){
        var $end = $('#end-test-confirm');

        // if checked, enable the end test confirm button
        if($(this).is(':checked'))
        {
            $end.prop('disabled', false);
            $end.removeClass('btn-default').addClass('btn-primary');
        }
        else
        {
            $end.prop('disabled', true);
            $end.removeClass('btn-primary').addClass('btn-default');
        }
    });

    // clicking 'I want to end this test' in popup
    $('#end-test-confirm').click(function(e){

        // Click our submit button with JS
        $('#end-test').click();

        // Now disable all other buttons until the request completes
        $('button, input[type="button"], input[type="submit"]').prop('disabled', true);
        
        return true;
    });

    // disable the submit (works via regular submit without modal if JS off)
    $('#end-test').click(function(e){

        // Is this an actual click?
        if(e.which)
        {
            // actually clicked
            e.preventDefault();
        }
        else
        {
            e.stopPropagation();
        }

        //else programmatically triggered, do normal action        
        return true;
    });

    // on click end test button, check if any questions remain unanswered and warn the user
    $('#stop-modal').on('shown.bs.modal', function (e) {
        // check if any questions remain
        var text = $('.questions-remaining').text();

        // if none remaining, don't do anything
        if (text.indexOf('None') != -1) {
            return;
        }

        // Otherwise, expect numbers, if there's only one number and it matches current item, 
        if ($.trim(text) == $('#current-question').val()) {
            // does current item have answer selected? 
            // (but not saved / gone through ajax update?)? If so, all answered, return
            if ($('.distractor input:checked').length > 0) {
                return;
            }
        }

        // Finally, if we made it here, show a warning about unanswered questions in modal!
        // remove any old warnings
        $('#stop-modal .modal-body .alert-warning.remaining').remove();

        var $warning = $('<div/>').addClass('alert alert-warning remaining').html(
            '<strong>Warning!</strong> - you have unanswered questions remaining - <strong>' + text + '</strong>'
        );
        $('#stop-modal .modal-body .alert-danger').before($warning);
    });

    var $questions = $('.distractor');

	// Distractor border / color on selection
	$('.distractor input:checked').parent('label').addClass('selected');
	$('body').on('click', '.distractor', function(){
		$('.selected').removeClass('selected');
		$(this).addClass('selected');
	});


	/**
	 * TIMER + COUNTDOWN STUFF
	 */
    // ... The FINAL COUNTDOWN... dun dun DEE dun
    // get seconds remaining
    var remaining = $('#remaining').val();
    // start interval
    countdown = setInterval(function(){

        // conversions to get hours, mins, seconds LEFT
        var h = Math.floor(remaining / 3600);
        var m = Math.floor((remaining % 3600) / 60);
        var s = Math.floor((remaining % 60));

        // Add leading zeroes if needed!
        if(h < 10)
        {
            h = '0'+h;
        }
        if(m < 10)
        {
            m = '0'+m;
        }
        if(s < 10)
        {
            s = '0'+s;
        }

        if ((remaining == "0") || (remaining <= 0)) {
            // Time Expired! Do something!
            $('#time-remaining').html('00:00:00');
            window.clearInterval(countdown);
            $('#end-test').click();
        }
        else
        {
            $("#time-remaining").html(h+':'+m+':'+s);
        }

        remaining--;

    }, 1000);	


    /**
     * HOTKEYS
     */
    // Left arrow key pressed, previous!
    $(document).bind('keydown', 'left pagedown', function(e) {
        e.preventDefault(); // prevent scrolling
        $('#prev').trigger('click');
    });
    // Right arrow key pressed, next!
    $(document).bind('keydown', 'right pageup', function(e) {
        e.preventDefault(); // prevent scrolling
        $('#next').trigger('click');
    });

    // A key
    $(document).bind('keydown', 'a A 1', function(e) {
        choiceHotkey(0);
    });
    // B key
    $(document).bind('keydown', 'b B 2', function(e) {
        choiceHotkey(1);
    });
    // C key
    $(document).bind('keydown', 'c C 3', function(e) {
        choiceHotkey(2);
    });
    // D key
    $(document).bind('keydown', 'd D 4', function(e) {
        choiceHotkey(3);
    });
    // E key
    $(document).bind('keydown', 'e E 5', function(e) {
        choiceHotkey(4);
    });
    $(document).bind('keydown', 'j J', function(){
        $('#jump-to-btn').trigger('click');
    });
    $(document).bind('keydown', 'h H', function(){
        $('#help-modal').modal('toggle');
    });

    var $form = $('#testing_form');
    var fromAjax = false; // for tracking whether a state change came from the ajax handler or not

  // Bind to StateChange Event
    History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
        var State = History.getState(); // Note: We are using History.getState() instead of event.state
        var btnName = State.data.btn;


        // // if the question is different from current question, ajax load this (probably back/forward buttons)
        // if(State.data.question != $('#current-question').val())
        // {
        //     $('#current-question').val(State.data.question);
        //     $('.jump-to-text').val(State.data.question);
        //     ajaxUpdateUI('');
        // }
        // 
        
        if( ! fromAjax)
            window.location.href = State.url;

        fromAjax = false;

    }); // History.statechange


    $('.btn-ajax').click(function(e){

        if($(this).prop('disabled') === true)
            return false;
        
        // stop regular form submit
        e.preventDefault();
    
        // Get the clicked button name to push with the request
        var btnName = $(this).prop('id');
        var q = $('#current-question').val();

        ajaxUpdateUI(btnName);
    }); // .btn-ajax cliff handler

    function ajaxUpdateUI(btnName)
    {
        fromAjax = true;

        // get name of this button to append it
        var $jumpText = $('.jump-to-text');

        if(btnName)
        {
            // Add loading indicator to button
            var l = Ladda.create(document.querySelector('#'+btnName));
            l.start();

            var formData = $form.serialize()+'&'+btnName+'=true';
        }
        else
        {
            var formData = $form.serialize(); 
        }

        // AJAX for test navigation
        $.ajax({
            type: 'POST',
            url: $form.prop('action'),
            data: formData,
            global: false,
            beforeSend: function(){
                disableControls(true);
            }
        })
            .done(function(result){
                disableControls(false);

                // stem
                $('.testing-stem').text('#'+result.current+'. '+result.stem);

                // current (hidden)
                $('#current-question').val(result.current);

                // Build the distractors
                var $distractors = $('.distractor');
                $distractors.removeClass('selected');
                $('input:radio', $distractors).each(function(){
                    $(this).prop('checked', false); 
                });

                $.each(result.distractors, function(index, value){
                    // build new distractors-list
                    if(index in $distractors)
                    {
                        $('input:radio', $distractors[index]).val(result.distractorIds[index]);
                        $('.distractor-content', $distractors[index]).text(value);
                        $($distractors[index]).parents('.form-group').show();

                        // Select the appropriate answer (if person has a response)
                        if(parseInt(result.distractorIds[index]) === parseInt(result.response))
                        {
                            $($distractors[index]).addClass('selected');
                            $('input:radio', $distractors[index]).prop('checked', true);
                        }

                    }
                }); 

                // Enable / disable buttons depending on question #
                toggleBtn($('#next'), result.current == result.total);
                toggleBtn($('#prev'), result.current <= 1);

                var $bookmarks = $('.bookmarks');
                $bookmarks.html('');

                // Bookmarks
                $.each(result.bookmarks, function(index, value){

                    var active = value == result.current ? 'btn-info' : 'btn-primary';                        

                    $bookmarks.append($('<a>', {
                        href: '/testing/'+(value),
                        "class": 'btn btn-sm '+active,
                        text: value
                    })).append(" ");
                });
                // update bookmark field itself
                $('#bookmark').val(result.current);
                if(result.bookmarks.length > 0)
                    $('#bookmark').prop('checked',  result.bookmarks.indexOf(result.current+"") > -1);
                else
                    $('.bookmarks').html($('<span>').addClass('text-muted').text('None'));

                // Questions Remaining
                if(result.remaining.length > 0)
                    $('.questions-remaining').text(result.remaining.join(", "));
                else
                    $('.questions-remaining').text("None. Please Review your answers");

                // show warning messages if any
                if(result.messages.warning != undefined)
                {
                    $('.panel-body .alert-danger').hide(); // hide existing
                    $('.testing-stem').before(buildAlert(result.messages.warning, 'warning')); // show new messages
                }

                // clear the jump to button
                $jumpText.val('');

                // push the new state to the browser
                History.pushState({question: result.current, btn: btnName}, 'Question '+result.current, '/testing/'+result.current)

            }) // end success
            .fail(function(){

                $('.testing-stem').before(buildAlert('Error getting testitem. Please try again. If this continues contact a test proctor.'))

            })
            .always(function(){
                // remove loading spinner
                if(l != undefined)
                    l.stop();
            }); // end .ajax()
    }

    function disableControls(enable)
    {
        $('#prev').prop('disabled', enable);    
        $('#next').prop('disabled', enable);    
        $('#jump-to-btn').prop('disabled', enable);  
        $('input[type="radio"]').prop('disabled', enable);  
    }

    function toggleBtn($obj, disable)
    {
        // swap and toggle classes
        $obj.prop('disabled', disable);

        if(disable === false)
            $obj.addClass('btn-primary').removeClass('btn-disabled');
        else
            $obj.addClass('btn-disabled').removeClass('btn-primary');
    }   

    function choiceHotkey(index) {
        var $choices = $('.distractor');
        var $new     = $('.distractors-list input:radio:nth(' + index + ')');
        var disabled = $('.distractors-list input:radio:disabled');

        // if there are disabled choices, don't change anything
        if (disabled.length) {
            return;
        }

        // Otherwise, start by unselecting all of them
        $choices.removeClass('selected');

        // Now check the input and mark its label as selected!
        $new.prop('checked', true);
        $new.parent('label').addClass('selected');
    } 

});	//document.ready
