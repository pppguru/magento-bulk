/* [@pack@] */

var _aniGoFor = 283;
var _aniGoDX = 40;
var _aniGoTime = 60;
var _count = 0;
var _target='slide_line', _dragx=null;
var _dragging=false, _animate=false;
var _left=0, _xspeed=0;
var _curLeft = 0;
var _startPageX = 0;
var _dX = 0;
var _startPageY = 0;
var _dY = 0;
var _mode = false; //It can be 'y'/'x'/false
var _matrix = null;


var _debug = true;
function debugMessage(message){
    if (_debug && $j('div.debug')){               
        _matrix = new WebKitCSSMatrix(window.getComputedStyle($(_target)).webkitTransform);
		var _realLeft = parseInt(_matrix.e);

        $j('div.debug').html(
            ((typeof(message) != 'undefined') ? message + '<br />' : '') +
            'dX = ' + _dX + '; dY = ' + _dY + ' xspeed = ' + _xspeed + ' <br/>' +
            'curLeft = ' + _curLeft + '; left = ' + _realLeft + ' <br/>' +
            'mode = ' + (_mode === false ? 'false' : _mode) + ' dragging = ' + _dragging + ' <br/>'
        );
    }
}


var MobileMediaButtons = Class.create();
MobileMediaButtons.prototype = {
    initialize: function(){
        this.aniGo = false;
        this.buttons = new Array();
        this.buttons['right'] = false;
        this.buttons['left'] = false;

        $j(window).bind('resize', function(e){
            mobileButtons.checkLeft();
            if (getScreenWidth() < ( $j('#slide_line').width() + 34)){
                $j('a.abutton').css('display', 'block');
            } else {
                $j('a.abutton').css('display', 'none');
            }
        }, false);

    },
    mouseDown: function(key){
        this.mouseUp();
        this.buttons[key] = true;

        if (!this.aniGo){
            this.animateSlider();
        }
    },
    mouseUp: function(){
        this.buttons['left'] = false;
        this.buttons['right'] = false;
    },
    needAnimate: function(){
        return (this.buttons['left'] || this.buttons['right']);
    },
    checkLeft: function(){       
        if ($j('#slide_line').css('left').replace('px', '') >= 34){
            this.mouseUp();
            this.aniGo = true;
            $j('#slide_line').animate({left: '17px'}, _aniGoTime * 7, function(){mobileButtons.aniGo = false;});
        } else if ($j('#slide_line').css('left').replace('px', '') <= 0 - $j('#slide_line').width() + getScreenWidth() - 34  ) {
            this.mouseUp();
            this.aniGo = true;
            $j('#slide_line').animate({left: (0 - $j('#slide_line').width() + getScreenWidth() - 17) + 'px'}, _aniGoTime * 7, function(){mobileButtons.aniGo = false;});
        } else {
            mobileButtons.animateSlider();
        }       
    },
    animateSlider: function(){
        this.aniGo = true;

        if (this.needAnimate()){
            if (this.buttons['left']){
                $j('#slide_line').animate({left: '+=' + _aniGoDX + 'px'}, _aniGoTime, function(){
                    mobileButtons.checkLeft();
                });
            } else {
                $j('#slide_line').animate({left: '-=' + _aniGoDX + 'px'}, _aniGoTime, function(){
                    mobileButtons.checkLeft();
                });
            }
        } else {
            this.aniGo = false;
        }       
    }
};

var mobileButtons = null;
function showNavigationButtons(){  
    if (getScreenWidth() < ( $j('#slide_line').width() + 34)){
        $j('a.abutton').css('display', 'block');        
    }
    mobileButtons = new MobileMediaButtons();
};


function targetAnimate(value, postback){
    if ($(_target)){
        var element = $j('#'+_target);        
        if (!element.hasClass('animate')){
            var callback = function(e){
                element.removeClass('animate');              
                if (typeof(postback) != 'undefined'){
                    postback();
                }
            };
            element.addClass('animate');
            $j('div.debug').addClass('red');
            element.one('webkitTransitionEnd', callback);
            $(_target).style.webkitTransform = value;
        }
    }
}

function getdXdY(){
    return (Math.abs(_dY) !== 0) ? Math.round(Math.abs(_dX) / Math.abs(_dY)) : Math.round(Math.abs(_dX));
}

function getdYdX(){
    return (Math.abs(_dX) !== 0) ? Math.round(Math.abs(_dY) / Math.abs(_dX)) : Math.round(Math.abs(_dY));
}

function getMaxCoord(){
    return (Math.abs(_dX) > Math.abs(_dY)) ? Math.abs(_dX) : Math.abs(_dY);
}

function checkLeftValue(){
    /* Check for extra Left */
    var maxLeft = 0;
    var minLeft = 0 - (_count * 283) + getScreenWidth() - 34;
    if (_curLeft > maxLeft){
        _curLeft = maxLeft;
        targetAnimate('translate3d('+maxLeft+'px, 0, 0)');
    } else if (_curLeft < minLeft) {
        _curLeft = minLeft;
        targetAnimate('translate3d('+minLeft+'px, 0, 0)');
    }
}

function touchstart(e){
	_target = this.id;
	_dragx = this.opts.dragx;
	_animate = this.opts.animate;


	_xspeed = 0;
    _dX = 0;
    _dY = 0;
    _mode = false;
	$j(e.changedTouches).each(function(){
        _matrix = new WebKitCSSMatrix(window.getComputedStyle($(_target)).webkitTransform);
        _curLeft = parseInt(_matrix.e);
		if(!_dragging){
			_left = _curLeft;
            _startPageX = this.pageX;
            _startPageY = this.pageY;
			_dragging = true;
		}
	});
    
    if ($j.support.isAndroid){
        e.preventDefault();
    }    
};

function touchmove(e){
	$j(e.changedTouches).each(function(){
        _dY = this.pageY - _startPageY;
        _dX = this.pageX - _startPageX;

        if (_mode === false){
            if ( (Math.abs( getdXdY() - getdYdX() ) > 3) ){
                _mode = (getdXdY() > getdYdX()) ? 'x' : 'y'
            } else if (getMaxCoord() > 5) {
                _mode = (Math.abs(_dX) > Math.abs(_dY)) ? 'x' : 'y';
            }
            if (_mode !== false){
                _dX = 0;
                _dY = 0;
            }                        
        }

        if ((_mode === false) ||  (_mode === 'x')){
            e.preventDefault();  
        }        
        if ((_mode === false) || (_mode === 'y')){
            //Block all
            if ($j.support.isAndroid){
                if (Math.abs(_dY) > 2){
                    window.scrollBy(0,-_dY);                  
                }
            }            
            return false;    
        }
        
        if(_dragging && _animate) {
            _matrix = new WebKitCSSMatrix(window.getComputedStyle($(_target)).webkitTransform);
            var _lastleft = (isNaN(parseInt(_matrix.e))) ? _curLeft : parseInt(_matrix.e);
        }

		_left = (_dX + _curLeft);
		
		if(_dragging) {            
			if(_animate){
				_xspeed = Math.round((_xspeed + Math.round( _left - _lastleft))/1.5);
			}			
			if(_dragx) {
                $(_target).style.webkitTransform = 'translate3d('+_left+'px, 0, 0)';
            }
		}
        
	});    
};
function touchend(e){
	$j(e.changedTouches).each(function(){
		if(!e.targetTouches.length){
			_dragging = false;
            _matrix = new WebKitCSSMatrix(window.getComputedStyle($(_target)).webkitTransform);
            _curLeft = _left = parseInt(_matrix.e);
            var animx = (Math.abs(_xspeed) > 0) ? 'translate3d('+(_left + _xspeed)+'px, 0, 0)' :  'translate3d('+_left+'px, 0, 0)';
            if(_dragx) {
                if (Math.abs(_xspeed) > 0){
                    targetAnimate(animx, function(e){
                        _matrix = new WebKitCSSMatrix(window.getComputedStyle($(_target)).webkitTransform);
                        _curLeft = _left = parseInt(_matrix.e);
//                        _mode = false;
                        checkLeftValue();
                        
                    });
                } else {
                    checkLeftValue();
                }
            }

		}
	});      
};

/* Init Mobile Navigation CLass */
var MobileMedia = Class.create();
MobileMedia.prototype = {
    initialize: function(sline, count){
        _count = count;
        this.sline = sline;
        sline.css({width:(count * 284) + 'px'});
        $j('#'+sline.attr('id')).each(function(index, element){
            this.opts = {animate: true, dragx: (count > 0)};
            element.addEventListener("touchstart", touchstart, false);
            element.addEventListener("touchend", touchend, false);
            element.addEventListener("touchmove", touchmove, false);

            if ($j.support.WebKitCSSMatrix){
                _matrix = new WebKitCSSMatrix(window.getComputedStyle($(_target)).webkitTransform);
                _curLeft = _left = parseInt(_matrix.e);
            }
        });
        $j(document).ready(function(e){
            $j(document).bind('checkorientationchange', function(e){
                checkLeftValue();
            });

            if ($(_target)){
                $(_target).style.webkitTransform = 'translate3d(-10px, 0, 0)';
                $(_target).style.webkitTransform = 'translate3d(0px, 0, 0)';
            }
        });        
    }
}

