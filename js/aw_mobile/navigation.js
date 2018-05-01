/* [@pack@] */

/* Enable debug to console process */
var useDblClick = true;
var debug = false;
var dumbLoop = null;
var canGo = true;
var lastElement = null;
var _touchMutex = true;
var _isParking = false;
var _isSwipe = false;
var catPath = new Array();
var catUrl = new Array();
var homeCatId = null;
var _tempTimer = null;

var _tapCatId = null;
var _tapCount = 0;
var _clickTimer = null;
var _drag = false;

var _startX, _startY, _lastX, _lastY = 0;

var debugMessage = function(message){
    $j('div.debug').html(message + ' CG = ' + (canGo ? 'true' : 'false') + '<br />'+ 'drag = ' + (_drag ? 'true' : 'false'));
};

var tapMe = function(element){
    if (debug){
        debugMessage('Tap');
    }
};

var hideMessages = function(){
    if ($j('ul.messages')){
        $j('ul.messages').addClass('hidden');
    }
};

var setContainerHeight = function(height){
    if (typeof(height) != 'undefined'){
        $j('div.header-nav-container').height(height);

        if ($j.support.isAndroid || $j.support.isBlackBerry){
            var sH = $j(window).height() - $j('div.footer-container').height() - $j('div.header-top').height() - $j('div.shop-access').height();
            if ($j('div.header-nav-container').height() < sH){
//                console.log(sH);
                $j('div.header-nav-container').height(sH);
            }
            scrollTo(0, 0);
        }
    }
};

function pause(millis)
{
    var date = new Date();
    var curDate = null;

    do {curDate = new Date();}
    while(curDate-date < millis);
}

var dumbLoopStart = function(){
    /* Handle Back Button Press */
    dumbLoop = setInterval(function(){
        var curid = $j('div.current').attr('id');
        if ((location.hash == '')) {
            location.hash = '#' + curid;
        } else if ((location.hash != '#' + curid)) {
            try {
//                if (debug) console.info('Back pressed');
                if (canGo){
                    navigation.goBack();
                }
            } catch(e) {
//                console.error('Unknown hash change.');
            }
        }
    }, 100);
};

/**
 * Set left to given element
 * @return null
 */
var setNavLeft = function(element, left){
    if (debug){
        debugMessage('setting left = ' + left + 'px');
    }
    if (element && element.attr('id')){
        $(element.attr('id')).style.webkitTransform = 'translate3d('+left+'px, 0, 0)';
    }
};

/**
 * Retrives history existanse flag
 * @return boolean
 */
var hasHistory = function(){
    return (navigation.history.length > 0);
};

/**
 * Retrives last object from history
 * @return null|jQuery
 */
var getLastFromHistory = function(){
    if (hasHistory()){
        return navigation.history[navigation.history.length - 1]
    }
    return null;
};

var restoreHistory = function(catId){

    if (typeof(catPath[catId]) != 'undefined'){
        var catIds = catPath[catId].split('/');
        var canDo = false;
        $(catIds).each(function(element){
//            if (debug) {console.log(element);}
            if (element == homeCatId){
                navigation.history.push($j('div#home'));
                canDo = true;
            } else {
                if (canDo){
                    if (element != $($j('div.current').attr('id')).category_id){
                        navigation.history.push($j('div#category' + element));
                    }                    
                }
            }
        });
        return true;
    }
    return false;
};

/* Init Mobile Navigation CLass */
var MobileNavigation = Class.create();
MobileNavigation.prototype = {
    initialize: function(){
        this.touchSelector = 'a.to_child';
        this.tapSelector = 'li.arrow a';
        this.history = new Array();
        setContainerHeight($j('div.current').height());

        if (debug){
//            console.info('Navigation initialized');
            debugMessage('Init navigation');
        }

        if (!$j.support.isMobile){
            $j(document).ready(function(e){
                $j('li.arrow a').each(function(index, element){
                    $j(element).mouseover(function(event){
                        $j(this).addClass('hover');
                    });
                    $j(element).mouseout(function(event){
                        $j(this).removeClass('hover');
                    });
                });
            });
        }
       
        /* Events handlers */
        $j(document).bind('ready', {self: this}, function(event){
            var touchSelector = event.data.self.touchSelector;
            var tapSelector = event.data.self.tapSelector;
            if (debug){
//                console.info('Handlers to:' + touchSelector);
            }

            if ($j.support.touch || $j.support.isMobile){
                $j(tapSelector).bind('touchstart', {self: event.data.self}, function(event){                                        
                    var hoverInterval = setInterval(function(e){
                        if (_touchMutex){
                            navigation.resetOver();
                            $j(event.target).addClass('hover');
                            _tempTimer = setInterval(function(e){
                                _touchMutex = true;
                                clearInterval(_tempTimer);
                                _tempTimer = null;
                            }, 500);

                        } else {
                            $j('li.arrow a.hover').removeClass('hover');
                            _tempTimer = setInterval(function(e){
                                _touchMutex = true;
                                clearInterval(_tempTimer);
                                _tempTimer = null;
                            }, 500);

                        }
                        clearInterval(hoverInterval);
                        hoverInterval = false;
                    }, 150);
                });
            }

            /* Slide links */
            $j(touchSelector).bind('click', {self: event.data.self}, function(event){
                event.data.self.handleClick(event.currentTarget);
                return false;
            });

            if ((document.location.hash != '') && (document.location.hash != '#home') && (document.location.hash != '#cart')){
                $j('div.current').removeClass('current');
                $j('div'+document.location.hash).addClass('current');
                setContainerHeight($j('div.current').height());
                if (typeof(document.getElementById($j('div.current').attr('id')).category_id) != 'undefined'){
                    restoreHistory(document.getElementById($j('div.current').attr('id')).category_id);
                }                
            } else {                
                if (document.location.hash == '#cart'){
                    $j(document).ready(function(){
                        $j('div.content-container').addClass('hidden');
                        $j('div.cart-container').removeClass('hidden');
                    });
                }else {
                    document.location.hash = 'home';
                }
            }            
            dumbLoopStart();

            var startDrag = function(){                
                /* Stop handling other events */
                canGo = false;
                clearInterval(dumbLoop);

                $j('div.current').addClass('drag');

                if (hasHistory()){
                    lastElement = getLastFromHistory();
                    if (lastElement.height() > $j('div.header-nav-container').height()){
                        setContainerHeight(lastElement.height());
                    }
                    setNavLeft(lastElement, -getScreenWidth());
                    lastElement.addClass('visible');
                }
                _drag = true;

            };
            
            var dragTo = function(dX, dY){
                var MagicDigit = Math.round(((Math.abs(dX) - Math.abs(dY)))/(Math.abs(dX)) * 100);
                if (debug){debugMessage('drag = ' + (_drag ? 'true' : 'false'));}
                if ((dX > 0) &&  (MagicDigit > 90) &&  (_drag == false)){
                    startDrag();                    
                }
                
                if ((dX > 0) && hasHistory() && (_drag == true) ){
                    /* Change position */
                    setNavLeft($j('div.current'), dX);
                    setNavLeft(lastElement, dX - getScreenWidth());
                }
            };

            var stopDrag = function(dX){
                if (_drag == true){
                    setNavLeft($j('div.current'), 0);
                    
                    if (lastElement !== null){
                        setNavLeft(lastElement, 0);
                        lastElement.removeClass('visible');
                    }

                    if ( dX > Math.round(getScreenWidth() / 3) ){
                        canGo = true;
                        navigation.goBack(dX);
                    } else {
                        _isParking = true;
                        navigation.parking();
                    }

                    setContainerHeight($j('div.current').height());

                    canGo = true;
                    _drag = false;
                }
            };
         
            if ($j.support.touch || $j.support.isMobile){

                var ontouchstart = function(e){
                    $j(e.changedTouches).each(function(){
                        if (e.targetTouches.length > 1) return false;

                        _lastX = _startX = this.pageX;
                        _lastY = _startY = this.pageY;

                        if (useDblClick){
                            if (_isAnimate){
                                return false;
                            }

                            var categoryId = 0;
                            if (typeof(e.target.hash) != 'undefined'){
                                categoryId = e.target.hash.replace('#category', '');
                            }

                            if (_clickTimer !== null){
                                _tapCount++;
                            } else {

                               _tapCatId = categoryId;
                               _tapCount = 1;
                               _clickTimer = setInterval(function(event){
                                    if (_tapCount >= 2 && _isSwipe == false){
                                        if (categoryId > 0){
                                            window.location = catUrl[categoryId];
                                        } else {

                                            var link = e.target.toString();
                                            if (link.length && link[0] != '[') {
                                                window.location = e.target;
                                            }                                            
                                        }
                                    }

                                    _tapCatId = null;
                                    _tapCount = 0;
                                    _isSwipe = true;
                                    clearInterval(_clickTimer);
                                    _clickTimer = null;
                               }, 300);
                            }
                        }

                        if (debug){
                            debugMessage('Touch started!');
                            $j('div.debug').addClass('red');
                        }
                    });
                };

                var ontouchend = function(e){
                    _isSwipe = false;
                    
                    $j(e.changedTouches).each(function(){
                        if (!e.targetTouches.length){
                            if (debug){
                                debugMessage('Touch finished!');
                                $j('div.debug').removeClass('red');
                            }

                            var dX = _lastX - _startX;
                            stopDrag(dX);
                            _touchMutex = false;
                            navigation.resetOver();
                        }
                    });
                };

                var ontouchmove = function(e){
                    $j(e.changedTouches).each(function(){

                        _lastX = this.pageX;
                        var dX = this.pageX - _startX;
                        var dY = this.pageY - _startY;
                        if ( (Math.abs(dY) > 0) || (Math.abs(dX) > 0) ){
                            _touchMutex = false;
                        }

                        dragTo(dX, dY);

                        if (debug){
                            var pos = 'dX = ' + dX + 'px; dY = ' + dY + 'px';
                            pos += '<br />';
                            debugMessage(pos);
                        }
                        return false;

                    });
                };

                $j('#main').each(function(){
                    this.ontouchstart = ontouchstart;
                    this.ontouchend = ontouchend;
                    this.ontouchmove = ontouchmove;
                });
            }
        });
    },
    animateNavigation: function(from, to, backwards){
        if (!canGo){
            return false;
        }

        var aniClass = 'slide';
        if(to.length === 0){
//            console.error('Target element is missing.');
            return false;
        }
        if (debug){
            debugMessage('Animation' + (backwards ? 'back' : ''));
        }

        if (to.height() > from.height()){
            if (debug){
//                console.info('Set height for MAIN to ' + to.height());
            }

            setContainerHeight(to.height());
        }

        $j(':focus').blur();

//        scrollTo(0, 0);


        var callback = function(event){
            from.removeClass('current out reverse ' + aniClass);
            to.removeClass('in reverse ' + aniClass + ' animated');
            
            from.trigger('pageAnimationEnd', {direction: 'out'});
            to.trigger('pageAnimationEnd', {direction: 'in'});

            setNavLeft(to, 0);

            if (debug){
                debugMessage('try to set height');
            }

            setContainerHeight(to.height());

            clearInterval(dumbLoop);
            currentPage = to;
            location.hash = currentPage.attr('id');
            dumbLoopStart();

            var $originallink = to.data('referrer');
            if ($originallink) {
                $originallink.unselect();
            }
            lastAnimationTime = (new Date()).getTime();

            if (debug){
                debugMessage('Animation end.');
//                console.info('Set main height: ' + to.height());
            }
            navigation.resetOver();
            canGo = true;
//            scrollTo(0, 0);
            hideMessages();

        };

        if ($j.support.WebKitAnimationEvent) {
            canGo = false;

            to.removeClass('drag touchBack');
            from.removeClass('drag touchBack');

            setNavLeft(to, 0);
            setNavLeft(from, 0);
            

            to.one('webkitAnimationEnd', callback);            
            from.addClass(aniClass + ' out' + (backwards ? ' reverse' : ''));
            
            to.addClass('current');
            to.addClass(aniClass + ' in' + (backwards ? ' reverse' : ''));




        } else {
            to.addClass('current');
            callback();
        }
        return true;        
    },
    goBack: function(backFrom){
       if ((this.history.length > 0) && canGo){
           targetPage = this.history.pop();
           currentPage = $j('div.current');

           if (backFrom > 0){               
               var callback = function(event){
                   targetPage.removeClass('touchBack drag');
                   currentPage.removeClass('touchBack drag');
                   if (debug){
                       debugMessage('Transition finished!');
                   }

                   setContainerHeight(targetPage.height());
                   setNavLeft(targetPage, 0);
                   setNavLeft(currentPage, 0);
                   
                   clearInterval(dumbLoop);
                   document.location.hash = '#' + $j('div.current').attr('id');
                   dumbLoopStart();
                   navigation.resetOver();
                   canGo = true;
                   return false;
               };
               canGo = false;               
               targetPage.one('webkitTransitionEnd', callback);
               targetPage.addClass('touchBack current');
               currentPage.addClass('touchBack').removeClass('current');
               setNavLeft(targetPage, 0);
               setNavLeft(currentPage, -getScreenWidth());
               if (debug){
                   debugMessage('Transition started!');
               }             

           } else {
               this.animateNavigation(currentPage, targetPage, true);
               return false;
           }
       }
       return true;
    },
    parking: function(){
        if ((this.history.length > 0) && _isParking){
            canGo = false;
            var currentPage = $j('div.current');
            var lastPage = getLastFromHistory();

            var parCallback = function(event){
                lastPage.removeClass('parking drag');
                currentPage.removeClass('parking drag');
                
                canGo = true;
                _isParking = false;
                navigation.resetOver();
            };

            currentPage.one('webkitTransitionEnd', parCallback);
            currentPage.addClass('parking');
            lastPage.addClass('parking');
            setNavLeft(currentPage, 0);
            setNavLeft(lastPage, -getScreenWidth());
        }
    },
    handleClick: function(target){
//        if (debug) console.info('touch: '+ target.hash);
        /* Get currnet location */                                
        currentPage = $j('div.current');
        targetPage = $j('div'+target.hash);

        if (!$j.support.isMobile) {
            if (useDblClick){
                if (_isAnimate){
                    return false;
                }

                var categoryId = target.hash.replace('#category', '');

//                if (debug){ console.log(categoryId); }

                if (_clickTimer !== null){
                    _tapCount++;
                } else {

                   _tapCatId = categoryId;
                   _tapCount = 1;
                   _clickTimer = setInterval(function(e){
                        if (_tapCount >= 2){
                            window.location = catUrl[categoryId];
                        } else {
                            navigation.navigate(currentPage, targetPage);
                        }
                        _tapCatId = null;
                        _tapCount = 0;
                        clearInterval(_clickTimer);
                        _clickTimer = null;
                   }, 300);


                }
            } else {
                this.navigate(currentPage, targetPage);
            }
        } else {
            /* Is mobile */
            this.navigate(currentPage, targetPage);
        }


    },
    navigate: function(currentPage,targetPage){
        this.history.push(currentPage);
        this.animateNavigation(currentPage, targetPage, false);        
    },    
    resetOver: function(){
        $j('li.arrow a.hover').removeClass('hover');
        $j('div.parking').removeClass('parking');
        $j('div.touchBack').removeClass('touchBack');
        _touchMutex = true;
    }
};
window.onpopstate = function(){
languageDropdown = document.getElementById('select-language');
    if(languageDropdown) {
        languageDropdown.setAttribute('onchange','setLocation(this.value'+"+'"+location.hash+"'"+')');
    }
currencyDropdown = document.getElementsByName('currency');
    if(currencyDropdown) {
        currencyDropdown[0].setAttribute('onchange','setLocation(this.value'+"+'"+location.hash+"'"+')');
    }
};
