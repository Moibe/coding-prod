// Init navigation menu
jQuery(function(){
	jQuery('.sf-menu').mobileMenu({defaultText: "Navigate to..."});
// main navigation init
    jQuery('ul.sf-menu').superfish({
        delay: 1000, // the delay in milliseconds that the mouse can remain outside a sub-menu without it closing
        animation: {
            opacity: "show",
            height: "show"
        }, // used to animate the sub-menu open
        speed: "normal", // animation speed
        autoArrows: false, // generation of arrow mark-up (for submenu)
        disableHI: true // to disable hoverIntent detection
    });

//Zoom fix
//IPad/IPhone
    var viewportmeta = document.querySelector && document.querySelector('meta[name="viewport"]'),
        ua = navigator.userAgent,
        gestureStart = function () {
            viewportmeta.content = "width=device-width, minimum-scale=0.25, maximum-scale=1.6, initial-scale=1.0";
        },
        scaleFix = function () {
            if (viewportmeta && /iPhone|iPad/.test(ua) && !/Opera Mini/.test(ua)) {
                viewportmeta.content = "width=device-width, minimum-scale=1.0, maximum-scale=1.0";
                document.addEventListener("gesturestart", gestureStart, false);
            }
        };
    scaleFix();
})

jQuery(document).ready(function(){

    

        function runEffect() {
      // get effect type from
      var selectedEffect = "blind";
 
      // Most effect types need no options passed by default
      var options = {};
      // some effects have required parameters
      if ( selectedEffect === "scale" ) {
        options = { percent: 50 };
      } else if ( selectedEffect === "size" ) {
        options = { to: { width: 200, height: 60 } };
      }
 
      // Run the effect
      $( "#effect" ).toggle( selectedEffect, options, 500 );
    };
 
    // Set effect from select menu value
    $( "#burguer" ).on( "click", function() {
      runEffect();
      
    });

    $(document).on('click', '.menu-mb-c li', function () {      
      $( "#effect" ).toggle(500);    
    });

    // MENU


    if(!device.mobile() && !device.tablet()){
        jQuery('.header .header-content').tmStickUp({
            correctionSelector: jQuery('#wpadminbar')
        ,   listenSelector: jQuery('.listenSelector')
        ,   active: false               ,   pseudo: true                });
    }
})

jQuery(document).ready(function($) {
        if(!device.mobile() && !device.tablet()){
            liteModeSwitcher = false;
        }else{
            liteModeSwitcher = true;
        }
        if($.browser.msie && parseInt($.browser.version) < 9){
             liteModeSwitcher = true;
        }

            jQuery('#parallax-slider-58e771280f371').parallaxSlider({
                parallaxEffect: "parallax_effect_normal"
            ,   parallaxInvert: true            ,   animateLayout: "simple-fade-eff"
            ,   duration: 1500          ,   autoSwitcher: true          ,   autoSwitcherDelay: 10000            ,   scrolling_description: false            ,   slider_navs: true           ,   slider_pagination: "none_pagination"
            ,   liteMode :liteModeSwitcher
            });

    });