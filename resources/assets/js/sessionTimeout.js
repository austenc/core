// Buffer between server side timeout and popup countdown
var bufferTime = 10;
// Time before the limit is reached to show the timer for, in seconds
var warnLength  = 60;
// Current page title
var pageTitle = document.title;
var isOldTitle = true;
 
var $modal;
var route;
var timeLeft;
var titleTimer;
var sessionTimer;
var countdownTime;
var countdownTimer;

$(window).load(function() {

    // if no modal, do nothing 
    if( ! $('#session-timeout-modal').length) {
        return;
    } else {
        $modal = $('#session-timeout-modal');
    }

    // Session time set via config var
    var minutesLeft = parseInt($('.time-limit', $modal).html());

    // Get logout route
    route = $('.logout-route', $modal).html();

    // calculate the time left in seconds until warning comes up
    // add an additional few seconds buffer in case it gets close to server side timeout
    timeLeft    = ((minutesLeft * 60) - bufferTime) - warnLength;

    // Set the countdown timer
    $('.timer', $modal).html(formatTime(warnLength));
    countdownTime = warnLength;

    // Calculate the amount of time the user has until logged out
    sessionTimer = setInterval(function() {
        inactivityModal();
    }, timeLeft * 1000);

    // On hide of timeout modal, refresh the page!
    $modal.on('hide.bs.modal', function() {
        clearInterval(countdownTimer);
        location.reload();
    });

    $(window).focus(function () {
        clearInterval(titleTimer);
        document.title = pageTitle;
    });
});

/**
 * Show a modal and start a countdown timer to let user know they've been inactive too long
 */
function inactivityModal() {
    // Start the timer for the countdown
    countdownTimer = setInterval(countdown, 1000);

    // Start the timer for flashing the page title
    titleTimer = setInterval(function() {
        document.title = isOldTitle ? '*TIME EXPIRING* ' + pageTitle : pageTitle;
        isOldTitle = !isOldTitle;
    }, 800);

    // show the modal 
    $modal.modal('show');
    clearInterval(sessionTimer);
}

/**
 * Update the countdown, flash page title, redirect if there's no time left
 */
function countdown() {
    if (countdownTime <= 0) {
        // clear our timer
        clearInterval(countdownTimer);
        // make sure timer can't show as negative
        $('.timer', $modal).html('00:00');
        // redirect to the logout page!
        window.location = route.length > 1 ? route : '/logout/1';

    } else {
        // update the countdown timer
        $('.timer', $modal).html(formatTime(countdownTime));

        // flash the page title 
        
    }

    countdownTime--;
}

/**
 * Format time to MM:SS value
 */
function formatTime(seconds) {
    var minutes = Math.floor(seconds / 60);
    var seconds = seconds % 60;

    return str_pad_left(minutes,'0',2)+':'+str_pad_left(seconds,'0',2);
}

/**
 * Pad value on the left with with a given character to a given length
 * @param  {string} string - input string you want to pad 
 * @param  {int} pad       - what value to pad the string with
 * @param  {int} length    - how long you want the padded string to be
 */
function str_pad_left(string, pad, length) {
    return (new Array(length+1).join(pad)+string).slice(-length);
}
