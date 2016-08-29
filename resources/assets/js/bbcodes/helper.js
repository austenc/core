/**
 * Parse a string for BBCode values and render HTML form field(s) out of them
 * @param   str     (string) the string to parse
 * @param   name    (string) the name of the parent element to tie form fields to
 */
function parseBBWeb(str, name){
    if(str === '')
    {
        return '';      
    }
    
    // main function's return string
    var parsed = str;
    
    // init vars used in each replace case
    var newStr = '';
    var returnMe = '';
    var splitArr = '';
    var fieldCount = 0;

    // Non-Empty RADIO buttons?
    var radioPattern =/((\[radio).*?]{1}(.)+?(\[\/radio]))/g;
    if(radioPattern.test(parsed) === true)
    {
        fieldCount = 1;
        //we've got a valid radio button bbCode, HTML it
        newStr = parsed.replace(/(\[radio])(.+?)\[\/radio]/g, function(match, $1, $2){
            returnMe = '';
            splitArr = $2.split('|');
            for(var i=0; i<splitArr.length; i++)
            {
                //make radio input for EACH button
                returnMe += '<input type="radio" name="'+name+'-radio-'+fieldCount+'" value="'+splitArr[i]+'" />'+splitArr[i]+"\n";
            }
            fieldCount++;
            return returnMe;
        });
        parsed = newStr;
    } // End RADIO check
    
    // Non-Empty DROPDOWN (select) box?
    var ddPattern = /\[dropdown].+?\[\/dropdown]/g;
    if(ddPattern.test(parsed) === true)
    {
        fieldCount = 1;
        //we've got a valid radio button bbCode, HTML it
        newStr = parsed.replace(/(\[dropdown])(.+?)\[\/dropdown]/g, function(match, $1, $2){
            returnMe = '<select name="'+name+'-dropdown-'+fieldCount+'">'+"\n";
            splitArr = $2.split('|');
            for(var i=0; i<splitArr.length; i++)
            {
                //make dropdown option for EACH delimit
                returnMe += '<option value="'+splitArr[i]+'">'+splitArr[i]+"</option>\n";
            }
            fieldCount++;
            return returnMe+'</select>'+"\n";
        });
        parsed = newStr;
    } // End DROPDOWN check
        
    // Non-Empty TEXTBOX (input) box?
    var tbPattern = /\[textbox].+?\[\/textbox]/g;
    if(tbPattern.test(parsed) === true)
    {
        fieldCount = 1;
        //we've got a textbox
        newStr = parsed.replace(/(\[textbox])(.+?)\[\/textbox]/g, function(match, $1, $2){
            returnMe = '';
            //returnMe +=   $2+' <input type="text" name="'+name+'-textbox-'+fieldCount+'" />';
            // UNCOMMENT THE ABOVE TO ADD FIELD NAME PRIOR TO FIELD
            returnMe += ' <input type="text" name="'+name+'-textbox-'+fieldCount+'" />';
            fieldCount++;
            
            return returnMe;
        });
        parsed = newStr;
    } // End DROPDOWN check

    return parsed;
}

/**
 * Parse a string for BBCode values and render a paper test version out of the fields
 * @param   str     (string) the string to parse
 * @param   name    (string) the name of the parent element to tie form fields to
 */
function parseBBPaper(str, name){
    if(str === '')
    {
        return '';      
    }
        
    // main function return string 
    var parsed = str;
    
    //init bbcode inner replacement vars
    var newStr = '';
    var returnMe = '';
    var splitArr = '';
    var fieldCount = 0;
    
    // RADIO field(s)
    var radioPattern =/((\[radio).*?]{1}(.)+?(\[\/radio]))/g;
    if(radioPattern.test(parsed) === true)
    {
        fieldCount = 1;
        //we've got a valid radio button bbCode, convert each to a 'circle one'
        newStr = parsed.replace(/(\[radio])(.+?)\[\/radio]/g, function(match, $1, $2){
            returnMe = 'Please Circle <strong>ONE</strong>: '+"\n\n";
            splitArr = $2.split('|');
            for(var i=0; i<splitArr.length; i++)
            {
                //make all split options into 'circle one' options
                if(i === 0)
                {
                    returnMe += splitArr[i];
                }
                else
                {                
                    returnMe += "  |  "+splitArr[i];
                }
            }
            fieldCount++;
            return returnMe+"\n";
        });
        parsed = newStr;
    }
    
    // Non-Empty DROPDOWN (select) box?
    var ddPattern = /\[dropdown].+?\[\/dropdown]/g;
    if(ddPattern.test(parsed) === true)
    {
        fieldCount = 1;
        //we've got a valid radio button bbCode, HTML it
        newStr = parsed.replace(/(\[dropdown])(.+?)\[\/dropdown]/g, function(match, $1, $2){
            returnMe = 'Please Circle <strong>ONE</strong>: '+"\n\n";
            splitArr = $2.split('|');
            for(var i=0; i<splitArr.length; i++)
            {
                //make all split options into 'circle one' options
                if(i === 0)
                {
                    returnMe += splitArr[i];
                }
                else
                {
                    returnMe += "  |  "+splitArr[i];
                }
            }
            fieldCount++;
            return returnMe+"\n";
        });
        parsed = newStr;
    } // End DROPDOWN check 

    // Non-Empty Text Input box?
    ddPattern = /\[textbox].+?\[\/textbox]/g;
    if(ddPattern.test(parsed) === true)
    {
        fieldCount = 1;
        //we've got a valid radio button bbCode, HTML it
        newStr = parsed.replace(/(\[textbox])(.+?)\[\/textbox]/g, function(match, $1, $2){
            returnMe = '';
            //returnMe +=   $2+' _______________________';
            // UNCOMMENT THE ABOVE TO INCLUDE FIELD NAME
            returnMe += ' _______________________';
            fieldCount++;
            return returnMe;
        });
        parsed = newStr;
    } // End DROPDOWN check 

    return parsed;
}

/**
 * Adjust position of input tag in a skill step
 * looks for input tag, replaces old tag, moves to new cursor pos
 */
function insertAtCaret(area, text) 
{    
    // get cursor position
    var txtarea = area[0];
    var scrollPos = txtarea.scrollTop;
    var strPos    = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?  "ff" : (document.selection ? "ie" : false ));
    if (br == "ie") 
    { 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        strPos = range.text.length;
    }
    else if (br == "ff") 
    {
        strPos = txtarea.selectionStart;
    }

    // find position of where old inputTag starts
    var oldPos = txtarea.value.indexOf(text);

    // removing inputTag will sometimes adjust cursor pos, fix it here
    if(strPos > oldPos)
    {
        strPos -= text.length;
    }

    // remove old input tag
    var replaced = txtarea.value.replace(text, '');
    txtarea.value = replaced;

    // add inputTag to new position
    var front = (txtarea.value).substring(0,strPos);  
    var back  = (txtarea.value).substring(strPos,txtarea.value.length); 
    txtarea.value = front+text+back;
    strPos = strPos + text.length;
    
    if (br == "ie") 
    { 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        range.moveStart ('character', strPos);
        range.moveEnd ('character', 0);
        range.select();
    }
    else if (br == "ff") 
    {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
    }
    txtarea.scrollTop = scrollPos;
}