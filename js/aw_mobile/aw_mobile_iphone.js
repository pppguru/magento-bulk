/* [@pack@] */

$j(function(){

    /* Android orientation support */
    var supportsOrientationChange = "onorientationchange" in window,
    orientationEvent = supportsOrientationChange ? "orientationchange" : "resize";

    window.addEventListener(orientationEvent, function(){
        checkLand(window.orientation);
        $j(document).trigger('checkorientationchange');
    }, true);
});

$j(document).ready(function(){
    checkLand(window.orientation);
    $j(document).trigger('checkorientationchange');
});


var checkLand = function(orientation){
    switch (orientation){
        case -90:
        case 90:
            $j('body').addClass('wide'); //Landscape
            break;
        case 0:
            $j('body').removeClass('wide'); //Profile
            break;
    }
};

/**
 * Set left to given element
 * @return null
 */
var setLeft = function(element, left){
    if (element && element.attr('id')){
        $(element.attr('id')).style.left = left + 'px';
    }
};

/**
 * Retrives with of screen
 * @return int
 */
var getScreenWidth = function(){
    var width = 320;
    if ($j(window).width()){
        return $j(window).width();
    }
    return width;
};

var isTouchDevice = function() {
    try {
        document.createEvent("TouchEvent");
        return true;
    } catch (e) {
        return false;
    }
};


var uagent = navigator.userAgent.toLowerCase();
$j.support.WebKitCSSMatrix = (typeof WebKitCSSMatrix == "object");
$j.support.touch = (typeof Touch == "object");
$j.support.WebKitAnimationEvent = (typeof WebKitTransitionEvent == "object");
$j.support.isAndroid = (uagent.search('android') > -1);
$j.support.isBlackBerry = (uagent.search('blackberry') > -1);

$j.support.isMobile = ($j.support.touch || $j.support.isAndroid || $j.support.isBlackBerry);

/**
 * Mobile Safari missing labels fix
 */
var registerLabels = function(){
    $$('label').each(
        function(el){
            if(el.readAttribute('for')){
                var _for = $(el.readAttribute('for'));
                if(_for instanceof Object){
                    el.onclick = function(el){return function(e){
                        if (['radio', 'checkbox'].indexOf(el.getAttribute('type')) != -1) {
                            el.setAttribute('selected', !el.getAttribute('selected'));
                        } else {
                            el.focus();
                        }
                    }}(_for)
                }
            }
        }
    );
};

document.observe('dom:loaded',	function () {
    registerLabels();
});

