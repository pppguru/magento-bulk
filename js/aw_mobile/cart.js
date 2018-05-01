/* [@pack@] */

var _isAnimate = false;
var _isDeleteActivate = false;
var _debug = false;
var _ajaxTimeout = 10000; //10 sec

var oldHash;
var cartAnimate = function(from, to, direction, postback){
    if ($j.support.WebKitAnimationEvent) {
        var footer = $('footer-container'); //is Prototype

        var callback = function(event){
            from.trigger('pageAnimationEnd', {direction: 'out'});
            to.trigger('pageAnimationEnd', {direction: 'in'});
            from.removeClass('animation from').removeClass(direction).addClass('hidden');
            to.removeClass('animation to').removeClass(direction).addClass('active');
            $j('div#footer-container').removeClass('animation');
            _isAnimate = false;
            if (direction == 'left'){
                mobileCart.updateOverlayHeights();
            }
            if (postback){
                postback();
            }
        };

        to.one('webkitAnimationEnd', callback);            
        from.removeClass('active');
        if (from.height() > to.height()){
            footer.style.top = (from.height() + 1) + 'px';
        } else {
            footer.style.top = (to.height() + 1) + 'px';
        }
        $j('div#footer-container').addClass('animation');
        to.removeClass('hidden');
        to.addClass('animation to').addClass(direction);
        from.addClass('animation from').addClass(direction);

              
    } else {
        from.removeClass('active').addClass('hidden');
        to.removeClass('hidden').addClass('active');
        _isAnimate = false;
        if (direction == 'left'){
            mobileCart.updateOverlayHeights();
        }
        if (postback){
            postback();
        }
    }
};

var goToCart = function(callback){
    if (_isAnimate){
        return false;
    }
    _isAnimate = true;
    cartAnimate($j('div.content-container'), $j('div.cart-container'), 'left', callback);
    oldHash = document.location.hash;
    document.location.hash = "#cart";
};

var cartBack = function(callback){
    if (_isAnimate){
        return false;
    }
    _isAnimate = true;
    cartAnimate($j('div.cart-container'), $j('div.content-container'), 'right', callback);
    if (oldHash != '#cart')
        document.location.hash = oldHash;
    else
        document.location.hash = '';
};

var _isLocalAnimate = false;
var animateItemOverlay = function(element, showAfter){
//    if ($j.support.WebKitAnimationEvent){
    if (false){
        if (_isLocalAnimate){return false;}
        _isLocalAnimate = true;
        var overlayId = element.attr('id');
        var containerId = 'button-' + overlayId;
        if (showAfter){
            /* Show */            
            $(overlayId).style.opacity = '1.0';
            $(overlayId).style.display = 'block';
            $j('div#' + containerId).width('0px');
            var localTimer = setInterval(function(){
                var buttonCallback = function(e){
                    $j('div#' + containerId).removeClass('trans-button');

                    _isLocalAnimate = false;
                };
                $j('div#' + containerId).addClass('trans-button');
                $j('div#' + containerId).one('webkitTransitionEnd', buttonCallback);
                $j('div#' + containerId).width('65px');
                clearInterval(localTimer);
            }, 100);           
        } else {
            /* Hide */
            element.removeClass('active');
            $(overlayId).style.opacity = '1.0';
            $(overlayId).style.display = 'block';
            $(containerId).style.width = '65px';

            var firstCallback = function(e){
                $j('div#' + containerId).removeClass('trans-button');

                var secondCallback = function(e){
                    $j('div#' + overlayId).removeClass('trans-overlay');
                    mobileCart.activeItem = null;
                    _isLocalAnimate = false;
                    
                };
                $j('div#' + overlayId).addClass('trans-overlay');
                $j('div#' + overlayId).one('webkitTransitionEnd', secondCallback);
                $(overlayId).style.opacity = '0.0';
            };
            $j('div#' + containerId).one('webkitTransitionEnd', firstCallback);
            $j('div#' + containerId).addClass('trans-button');
            $(containerId).style.width = '0px';
        }
    } else {
        if (showAfter){
            element.show();
        } else {
            element.hide();
            mobileCart.activeItem = null;
        }
    }   
};

var posToScreenCenter = function(element){
    var wH = $j(window).height();   
    element.css({top: (wH /2 - 12 + window.scrollY) + 'px'});
};

var callInProgress = function(xmlhttp) {
    switch (xmlhttp.readyState) {
        case 1: case 2: case 3:
            return true;
            break;
        // Case 4 and 0
        default:
            return false;
            break;
    }
};

var ajaxWrapper = function(url, params, preload){
    if (_debug){
        console.log('Ajax wrapper');
    }
    if (typeof(preload) == 'function'){
        preload();
    }

    Ajax.Responders.register({
        onCreate: function(request) {
            request['awMobileTimeoutId'] = window.setTimeout(                
                function() {
                    if (callInProgress(request.transport)) {
                        if (_debug){console.log('Exit for timeout');}
                        request.transport.abort();
                        if (request.options['onFailure']) {
                            request.options['onFailure'](request.transport, request.json);
                        }
                    }
                },
                _ajaxTimeout
            );
        },
        onComplete: function(request) {
            window.clearTimeout(request['awMobileTimeoutId']);
        }
    });

    new Ajax.Request(mobileCart.prepareUrl(url), params);
};

var MobileCart = Class.create();
MobileCart.prototype = {
    initialize: function(){
        this.activeItem = null;

        

    },
    registerGiftcard: function(giftcard_form_id, giftcard_url){
        this.giftcard_form = $j('#'+giftcard_form_id);
        this.giftcard_form_id = giftcard_form_id;
        this.giftcardFormSubmitUrl = giftcard_url;
        this.giftcard_form.submit(function(event){
            mobileCart.postGiftcardForm();
            return false;
        });

    },
    registerCoupon: function(coupon_form_id, coupon_url){
        this.coupon_form = $j('#'+coupon_form_id);
        this.coupon_form_id = coupon_form_id;
        this.couponFormSubmitUrl = coupon_url;

        this.coupon_form.submit(function(event){
            mobileCart.postCouponForm();
            return false;
        });

    },
    registerSubmit: function(form_id, url){
        this.form = $j('#'+form_id);
        this.form_id = form_id;
        this.formSubmitUrl = url;
        this.activeItem = null;
        
        this.form.submit(function(event){
            mobileCart.postCartForm();
            return false;
        });

        $j('div.cart-item-overlay').click(function(event){
            mobileCart.disableAllOverlays();
        });
        
        $j('div.cart-row').click(function(event){
            mobileCart.disableAllOverlays();
        });

        var dX = 0;
        $j('tr.cart-row').each(function(index, element){
            if ($j.support.touch || $j.support.isMobile){


                $(element).addEventListener('touchstart', function(event){
                    if (_isAnimate){return false;}

                    /* Stop handling other events */
                    this.lastX = this.startX = event.targetTouches[0].clientX;
                }, false);

                /**
                 * End of touch Handler
                 *
                 * Two ways:
                 *  - Screen go to place
                 *  - Screen go back
                 *  last: hide slide layer
                 */
                $(element).addEventListener('touchend',function(event){
                    if (_isAnimate){return false;}
                    dX = this.lastX - this.startX;               
                }, false);

                /**
                 * Moving of touch
                 *
                 * Movind slider by dX
                 */
                $(element).addEventListener('touchmove', function(event){
                    if (_isAnimate){return false;}
                    this.lastX = event.targetTouches[0].clientX;
                    var dX = event.targetTouches[0].clientX - this.startX;
                    if (dX > 50){
                        mobileCart.disableAllOverlays();
                        mobileCart.activeItem = $j('div#' + 'overlay-' + $j(this).attr('id'));
                        animateItemOverlay($j('div#' + 'overlay-' + $j(this).attr('id')), true);
                        _isDeleteActivate = true;

                    }
                    return false;
                }, false);
            } else {
                $(element).addEventListener('dblclick', function(event){
                    mobileCart.disableAllOverlays();
                    mobileCart.activeItem = $j('div#' + 'overlay-' + $j(this).attr('id'));
                    _isDeleteActivate = true;
                    animateItemOverlay($j('div#' + 'overlay-' + $j(this).attr('id')), true);                    
                }, false);
            }
        });      
    },
    disableAllOverlays: function(){
        if (this.activeItem != null){
            animateItemOverlay(this.activeItem, false);
        }
        _isDeleteActivate = false;
    },
    prepareUrl: function(url){
        var str = url;
        if (typeof(str) != 'undefined'){
            return str.replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, ''));
        } else {
            return url;
        }
        
    },
    setLoader: function(show, callback){
        if (show){
            posToScreenCenter($j('div.cart-loader'));
            showOverlay(callback, true);
        } else {
            hideOverlay(callback, true);
        }
    },
    updateContent: function(transport){

        try{
            response = eval('(' + transport.responseText + ')');
            if (response.cart_content) {
                $('main-cart').innerHTML = response.cart_content;
            }
            if (response.link_content){
                $('gotocart-button-container').innerHTML = response.link_content;
            }

            var cartNotEmpty = $('cart-form') instanceof Object;
            $$('#checkout-button').invoke(cartNotEmpty ? 'show' : 'hide');

            if (response.error){
                console.debug(response.error);
            }
        }
        catch (e) {
            response = {};
        }
        mobileCart.registerSubmit(mobileCart.form_id, mobileCart.formSubmitUrl);
        mobileCart.registerCoupon(mobileCart.coupon_form_id, mobileCart.couponFormSubmitUrl);
        
    },
    postProductForm: function(form_id, url){
        goToCart(function(){
            ajaxWrapper(url, {
                    method:'post',
                    onSuccess: function(transport){
                        if (transport && transport.responseText){
                            mobileCart.updateContent(transport);
                            mobileCart.setLoader(false);
                        }
                    },
                    onFailure: function(){
                        mobileCart.setLoader(false);
                    },
                    parameters: Form.serialize($(form_id))
                },
                function(){
                    mobileCart.setLoader(true);
                }
            );
        });
    },
    postGiftcardForm: function(url){
        mobileCart.disableAllOverlays();
        mobileCart.setLoader(true);
        
        var gotoUrl = this.giftcardFormSubmitUrl;
        if (typeof(url) != 'undefined'){
            gotoUrl = url;
        }
        
        ajaxWrapper(gotoUrl ,{
                method:'post',
                onSuccess: function(transport){
                    if (transport && transport.responseText){
                        mobileCart.updateContent(transport);
                        mobileCart.setLoader(false);
                    }
                },
                onFailure: function(){
                    mobileCart.setLoader(false);
                },                
                parameters: Form.serialize($(this.giftcard_form_id))
            });
    },
    postCouponForm: function(isRemove){
        if (isRemove) {
            $('coupon_code').removeClassName('required-entry');
            $('remove-coupone').value = "1";
        } else {
            $('coupon_code').addClassName('required-entry');
            $('remove-coupone').value = "0";
        }
        mobileCart.disableAllOverlays();
        mobileCart.setLoader(true);
        ajaxWrapper(this.couponFormSubmitUrl, {
                method:'post',
                onSuccess: function(transport){
                    if (transport && transport.responseText){
                        mobileCart.updateContent(transport);
                        mobileCart.setLoader(false);
                    }
                },
                onFailure: function(){
                    mobileCart.setLoader(false);
                },
                parameters: Form.serialize($(this.coupon_form_id))
            });
            
    },
    postCartForm: function(){       
        mobileCart.disableAllOverlays();
        mobileCart.setLoader(true);

        ajaxWrapper(this.formSubmitUrl, {
                method:'post',
                onSuccess: function(transport){
                    if (transport && transport.responseText){
                        mobileCart.updateContent(transport);
                        mobileCart.setLoader(false);
                    }
                },
                onFailure: function(){
                    mobileCart.setLoader(false);
                },
                parameters: Form.serialize($(this.form_id))
        });        
    },
    loadUpdatedCart: function(){
        mobileCart.setLoader(true);
        ajaxWrapper(this.contentUrl, {
            method: 'get',
            onSuccess: function(transport) {
                if (transport && transport.responseText){
                    mobileCart.updateContent(transport);
                    mobileCart.setLoader(false);
                }
            },
            onFailure: function() {
                mobileCart.setLoader(false);
            }
        });
    },
    updateOverlayHeights: function(){
        $j('div.cart-item-overlay').each(function(index, element){
            if (element.parentNode.offsetHeight > 0){
                $j(element).height( element.parentNode.offsetHeight - 1 );
            }
        });
    },
    deleteItem: function(deleteUrl){
        mobileCart.disableAllOverlays();
        mobileCart.setLoader(true);

        ajaxWrapper(deleteUrl, {
            method: 'get',
            onSuccess: function(transport) {
                if (transport && transport.responseText){
                    mobileCart.updateContent(transport);
                    mobileCart.setLoader(false);
                }
            },
            onFailure: function() {
                mobileCart.setLoader(false);
            }
        });
        return false;
    }
};

if (typeof(mobileCart) == 'undefined'){
    var mobileCart = new MobileCart();
}


$j(document).ready(function(){
    $j(document).bind('checkorientationchange', function(){
        mobileCart.updateOverlayHeights();
    });
    if (window.localStorage.getItem('awmobile_cart') == 'true') {
        window.localStorage.setItem('awmobile_cart', 'false');
        window.location.hash = "#cart";
    }
    oldHash = window.location.hash;
    if (window.location.hash == '#cart') {
        goToCart();
    }
    setLocation = function(url){
        if (window.location.hash == '#cart') {
            url += window.location.hash;
            window.localStorage.setItem('awmobile_cart', 'true');
        }
        window.location.href = url;
    }
});

/**
 * Handle document click
 */
var onClickDocument = function(event){
    if ((mobileCart.activeItem != null) && !$j(event.target).hasClass('cart-item-overlay') && !$j(event.target).hasClass('delete') && !$j(event.target.parentNode).hasClass('delete')){
        mobileCart.disableAllOverlays();
    }
};

if ($j.support.touch){
    document.addEventListener('touchstart', function(event){
        onClickDocument(event);
        return true;
    }, false);    
} else {
    document.addEventListener('click',function(event){
        onClickDocument(event);
        return true;
    }, false);
}

/**
 * Handle end of overlay hide
 */
$j(document).bind('hideoverlaycomplete', function(){
    mobileCart.updateOverlayHeights();
});

