$(document).ready(function(){

    // Save the menu's state in the session so it will persist page to page
    $('body').bind('expanded.pushMenu collapsed.pushMenu', function() {
        // send an ajax call to toggle the sidebar status
        $.ajax({
            url: '/users/toggle-sidebar',
            global: false
        });
    });

    // Javascript to enable link to opened tab within page
    var hash = document.location.hash;
    var prefix = "tab-";
    if (hash) {
        $('.nav-tabs a[href='+hash.replace(prefix,"")+']').tab('show');
    } 

    // Change hash for page-reload on tabs
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        history.replaceState(undefined, undefined, e.target.hash.replace("#", "#" + prefix));
    });

    // scrollable/tracking fixed sidebar
    $('body').scrollspy({ target: '.sidebar-contain' });

    // show loading gif on pageload
    $('[data-loader]').click(function(e){

        var href      = $(this).attr('href');
        var target    = $(this).attr('target');

        // Are they trying to open this action in a new tab? Middle click, Cmd Key, Shift Key, or Ctrl Key
        var modifiers = e.which == 2 || e.metaKey || e.shiftKey || e.ctrlKey;

        // if anchor isnt a bookmark, or data-confirm, or opening in a new tab.... then show loading 
        if(href && href.substr(0, 1) != "#" && ! $(this).is('[data-confirm]') && target != "_blank" && modifiers == false)
        {
            $("#main-ajax-load").show();
        }
    });

    // autosize any comment fields
    autosize($('#comments'));

    // Allow users to change role on changing the select
    $('#user-choose-role').on('change', function() {
        $(this).parents('form').submit();
    });

    // Clamped width for affixes
    $('[data-clampedwidth]').each(function () {
        var elem = $(this);
        var parentSelector = elem.data('clampedwidth');
        var $parentPanel = elem.data('clampedwidth');

        if (parentSelector) {
            $parentPanel = $(this).parent(parentSelector);
        } else {
            $parentPanel = $(this).parent('.sidebar');
        }

        var resizeFn = function () {
            var sideBarNavWidth = $parentPanel.width() - parseInt(elem.css('paddingLeft')) - parseInt(elem.css('paddingRight')) - parseInt(elem.css('marginLeft')) - parseInt(elem.css('marginRight')) - parseInt(elem.css('borderLeftWidth')) - parseInt(elem.css('borderRightWidth'));
            elem.css('width', sideBarNavWidth);
        };

        resizeFn();
        $(window).resize(resizeFn);
    });

    // Enable tooltips
    $(function () { $("[data-toggle='tooltip'], .make-tooltip").tooltip({container: 'body'}); });
    // Enable popovers
    $(function () { $("[data-toggle='popover']").popover({container: 'body'}); });

    // Fix dropdowns being cut off inside .table-responsive
    $('.table-responsive').on('show.bs.dropdown', function () {
         $('.table-responsive').css('overflow', 'inherit');
    });
    $('.table-responsive').on('hide.bs.dropdown', function () {
         $('.table-responsive').css('overflow', 'auto');
    })

    // 'Select All' checkboxes
    $(function () { 
        $('body').on('click', "[data-action='select-all']", function(){
            var rowClass = $(this).attr('data-toggle-class');
            var target   = $(this).attr('data-target');
            var checked  = $(this).prop('checked');

            $(target).prop('checked', checked);
            $(target).parents('tr').toggleClass(rowClass, checked);
        });
    });

    // Toggleable row colors from checkbox
    $(function () { 
        $('body').on('click', "[data-toggle-row]", function(){
           var rowColorClass = $(this).attr('data-toggle-row');
           $(this).parents('tr').toggleClass(rowColorClass);
        });
    });

    // 'clickable' row checkboxes
    $(function () {
        $('body').on('click', "[data-clickable-row]", function(e) {
            $(this).toggleClass('success');

            if( ! $(e.target).is(':checkbox'))
            {
                $(this).find(':checkbox').prop('checked', function (i, value) {
                    return !value; 
                }).change();
            }
        });
    });
     
    // generate fake email
    $(document).on('click', '#gen-email', function(){
        var href = $(this).data('href');

        $.ajax({
            url: href,
            dataType: "json",
            success: function(result){
                $('input[name="email"]').val(result);
            }
        });
    });

    // generate fake password
    $(document).on('click', '#gen-pwd', function(){
        var href = $(this).data('href');

        $.ajax({
            url: href,
            dataType: "json",
            success: function(result){
                $('input[name="password"]').val(result);
                $('input[name="password_confirmation"]').val(result);
            }
        });
    });

    // generate fake ssn
    $(document).on('click', '#ckbGenFakeSsn', function()
    {
        if($("#ckbGenFakeSsn").is(":checked"))
        {
            $.ajax({
                url: '/students/generate/ssn',
                success: function(data){
                    $("input[name=ssn]").val(data);
                    var rev = "";
                    for(var i = data.length - 1; i >= 0; i--)
                    {
                        rev += data[i];
                    }
                    $("input[name=rev_ssn").val(rev);
                }
            })
        }
        else
        {
            $("input[name=ssn]").val("");
            $("input[name=rev_ssn").val("");
        }
    });

    // Force modals to load new content (but not data-confirms!)
    $(document).on("hidden.bs.modal", function (e) {
        if($(e.target).prop('id') != 'dataConfirmModal' && ! $(e.target).hasClass('modal-preserve'))
        {
            $(e.target).removeData("bs.modal").find(".modal-content").empty();
            $(e.target).removeData("bs.modal").find(".modal-content").html($('#loading-contain').html());
        }
    });


    /**
     * Global / defaul Ajax Loading Indicator
     */
    $(document).ajaxStart(function(){
        showLoad = setTimeout(function() {            
            $("#main-ajax-load").fadeIn('fast');
        }, 250);
    });

    $(document).ajaxStop(function(){
        $("#main-ajax-load").fadeOut('fast');
        clearTimeout(showLoad);
    });

    // Back to top button
    $("#back-top").hide(); // hide scroll up button
    $(function () {
        $(window).scroll(function () {
            // scroll to top button
            if ($(this).scrollTop() > 100) {
                $('#back-top').fadeIn();
            } else {
                $('#back-top').fadeOut();
            }
        });
    });
    
    // ---------------------------------------------------------------------------------
    // ESC key to clear flash messages
    // ---------------------------------------------------------------------------------
    // Hit 'ESC' to clear main body alerts, ONE AT A TIME
    $(document).keyup(function(e){

        if(e.keyCode === 27)
            $('.flash-messages .alert .close:first').alert('close');
    });        

    // ---------------------------------------------------------------------------------
    // DATA MASK (password field mimic) feature
    // ---------------------------------------------------------------------------------
    
    // Make them 'password' type by default
    $('input[data-mask]').prop('type', 'password');
    $('input[data-mask]').focus(function() {
        this.type = "text";
    }).blur(function() {
        this.type = "password";
    });

    // ---------------------------------------------------------------------------------
    // MODAL CONFIRM BOXES!
    // ---------------------------------------------------------------------------------
    var confirmSelector = 'a[data-confirm], button[data-confirm], input[data-confirm]';
    $(confirmSelector).on('click', dataConfirm);

}); // document.ready

/**
 * Handle the data-confirm click event
 */
function dataConfirm(e, triggered) {
    var confirmSelector = 'a[data-confirm], button[data-confirm], input[data-confirm]';

    // event listeners if they exist
    // if this was a triggered click (via confirm OK), fall back to original 
    if (triggered && triggered == true) {
        return true;
    }

    // if unchecking checkbox, dont show data-confirm
    if ($(this).is(':checkbox') && ! $(this).prop("checked")) {
        return;
    }

    // Prevent Default action
    e.preventDefault();
    e.stopImmediatePropagation(); // stop any other events that may be tied to it

    // Remove any existing event handlers on confirm okay
    $('#dataConfirmOK').off('click');
    $('#dataConfirmOK').removeAttr('data-dismiss');

    if (!$('#dataConfirmModal').length) {
        $('body').append('<div id="dataConfirmModal" class="modal fade" role="dialog">'
            +'<div class="modal-dialog">'
            +'<div class="modal-content">'
            +'<div class="modal-header"><button type="button" class="close" data-dismiss="modal">×</button><h3 id="dataConfirmLabel">Please Confirm</h3></div>'
            +'<div class="modal-body"></div>'
            +'<div class="modal-footer"><button class="btn btn-danger" data-dismiss="modal" id="dataConfirmCancel">Cancel</button><a class="btn btn-primary" id="dataConfirmOK">OK</a></div>'
            +'</div>'
            +'</div>'
            +'</div>');
    }

    $('#dataConfirmModal').find('.modal-body').html($(this).attr('data-confirm'));

    // Does it have an href attribute?
    var href = $(this).prop('href');
    if (href) {
        // HAS an href attribute, use that as the OK
        $('#dataConfirmOK').prop('href', href);

    } else  {
        // NO href, remove data-confirm attribute from caller
        // attach event handler to OK button to trigger caller.click()
        var caller   = $(this);

        // Make the OK button dismiss the modal
        if ($(this).attr('data-modal-persist') == undefined) {
            $('#dataConfirmOK').attr('data-dismiss', 'modal');
        } else {
            $('#dataConfirmOK').removeAttr('data-dismiss');
        }

        // Trigger original click on confirm
        $('#dataConfirmOK').on('click', function(){
            // if this is a checkbox, also spawn the change event
            if (caller.is(':checkbox') || caller.is(':radio')) {
                caller.prop('checked', !caller.prop('checked'))
                caller.change();
            } else {
                caller.trigger('click', [true]);
            }
        });
    }

    $('#dataConfirmModal').modal({show:true});
}

function table_row_select(e, type)
{
    if( arguments.length == 1)
    {
        type = 'checkbox';
        type_action = 'checked';
    }
    else
    {
        type_action = 'selected';
    }

    var $target = $(e.target);

    if( ! $target.is('tr'))
    {
        var row = $target.parents('tr');
    }

    row.toggleClass('success');

    if ( ! $target.is(":"+type))
    {
        // Toggles Checkbox
        row.find(':'+type).prop(type_action, function (i, value) {
            return !value;
        });
    }
}

function buildAlert(messages, msgClass)
{   
    var html = ''

    // any messages to display?
    if(messages.length < 1)
        return html;

    // build the alert now
    html = '<div class="alert alert-'+msgClass+'">\n<ul>\n';
    $.each(messages, function(index, value){
        html += '<li>'+value+'</li>\n';
    });

    html += '</ul>\n</div>\n';

    return html;        
}

function flash(message, type, delay)
{
    // Default type to info
    if( ! type)
    {
        type = 'info';
    }

    // Default delay to 4000ms
    if( ! delay)
    {
        delay = 4000;
    }

    // Inject message and set class
    var $overlay = $('.flash-overlay');
    $overlay.text(message);
    $overlay.prop('class', 'flash-overlay alert alert-'+type);

    // Show the alert
    $overlay.fadeIn(300).delay(delay).fadeOut(300);
}

function showLoadOverlay(url){
    $("#main-ajax-load").show();
    window.location.href = url;
}
