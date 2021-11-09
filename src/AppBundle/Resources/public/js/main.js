$(document).ready(function () {

  $('form').card({
    container: '.card-wrapper',
    width: 280,

    formSelectors: {
        nameInput: 'input[name="card-name"]',
        numberInput:'input#number',
        expiryInput: 'input#month , input#year',
        cvcInput: 'input#ccv'
    },
    placeholders: {
        name: 'Nom Completo'
    }
});

  initForms();

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

    function initForms() {
      $(".validate-form").each(function () {
          $(this).validate({
              submitHandler: function (form) {
                  $('.loading-c').show();
                  $('.btn-conekta').text('Enviando...');
                  var options = {
                      success: function (data) {
                          $('.loading-c').hide();
                          $(".validate-form").find("input").val("");
                      }
                  };
                  $(form).ajaxSubmit(options);
              }
          });
      });
    }


});