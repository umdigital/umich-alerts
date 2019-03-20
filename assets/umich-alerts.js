(function(){
    var ready = function( fn ) {
        if( document.attachEvent ? document.readyState === 'complete' : document.readyState !== 'loading' ){
            fn();
        } else {
            document.addEventListener( 'DOMContentLoaded', fn );
        }
    };

    var hasClass = function( el, className ){
        if( el.classList ) {
            return el.classList.contains( className );
        }
        else {
            return (new RegExp('(^| )' + className + '( |$)', 'gi').test( el.className ));
        }
    }

    var addClass = function( el, className ){
        if( el.classList ) {
            el.classList.add( className );
        }
        else {
            el.className += ' '+ className;
        }
    }

    var remClass = function( el, className ){
        if( el.classList ) {
            el.classList.remove( className );
        }
        else {
            el.className = el.className.replace(
                new RegExp( '(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi' ),
                ' '
            );
        }
    }

    var getCookie = function( name ) {
        var v = document.cookie.match( '(^|;) ?' + name + '=([^;]*)(;|$)' );
        return v ? v[2] : null;
    }

    var setCookie = function( name, value, days ) {
        var d = new Date;
        d.setTime( d.getTime() + 24 * 60 * 60 * 1000 * days );
        document.cookie = name + "=" + value + ";path=/;expires=" + d.toGMTString();
    }

    var delCookie = function( name ) {
        setCookie( name, '', -1 );
    }

    ready(function(){
        if( (typeof umEAlertHtml !== 'undefined') && umEAlertHtml.length ) {
            var thisBodyEl = document.getElementsByTagName('body')[0];

            // append message to the body
            var tmpEl = document.createElement('div');
            tmpEl.innerHTML = umEAlertHtml;
            thisBodyEl.appendChild(
                tmpEl.firstChild
            );

            // position element to top of window
            var thisEAlert = document.getElementById('umich-alerts--ealert');
            thisEAlert.style.position = (getComputedStyle( thisBodyEl )['position'] == 'relative' ? 'fixed' : 'absolute');

            // set to collapsed iff same message that was collapsed
            if( getCookie('umich-alerts') == thisEAlert.getAttribute( 'data-uid' ) ) {
                addClass( thisEAlert, 'collapsed' );
            }
            else {
                delCookie( 'umich-alerts' );
            }

            // add collapse event
            thisEAlert.getElementsByClassName( 'umich-alerts-wrap' )[0]
                .addEventListener( 'click', function(){
                    if( hasClass( thisEAlert, 'collapsed' ) ) {
                        remClass( thisEAlert, 'collapsed' );

                        delCookie( 'umich-alerts' );
                    }
                    else {
                        addClass( thisEAlert, 'collapsed' );

                        setCookie( 'umich-alerts', thisEAlert.getAttribute( 'data-uid' ), 1 );
                    }

                    resizeEvent();
                });

            // handle resize event
            var resizeEvent = function(){
                document.getElementsByTagName('html')[0].style.setProperty(
                    'margin-top',
                    thisEAlert.offsetHeight +'px',
                    'important'
                );
            };
            resizeEvent();
            window.onresize = resizeEvent;
        }
    });
}());
