//Mobile Navigation
jQuery(document).ready(function ($) {
	$('.mobile-nav .toggle-button').on( 'click', function() {
		$('.mobile-nav .main-navigation').slideToggle();
	});

	$('.mobile-nav-wrap .close ').on( 'click', function() {
		$('.mobile-nav .main-navigation').slideToggle();

	});

	$('<button class="submenu-toggle"></button>').insertAfter($('.mobile-nav ul .menu-item-has-children > a, .mobile-nav ul .page_item_has_children > a'));
	$('.mobile-nav ul li .submenu-toggle').on( 'click', function() {
		$(this).next().slideToggle();
		$(this).toggleClass('open');
	});

	//accessible menu for edge
	 $("#site-navigation ul li a").on( 'focus', function() {
	   $(this).parents("li").addClass("focus");
	}).on( 'blur', function() {
	    $(this).parents("li").removeClass("focus");
	 });
});

//Scroll To Top
var ignite_agency_portfolio_btn = jQuery('#button');

jQuery(window).scroll(function() {
  if (jQuery(window).scrollTop() > 300) {
    ignite_agency_portfolio_btn.addClass('show');
  } else {
    ignite_agency_portfolio_btn.removeClass('show');
  }
});
ignite_agency_portfolio_btn.on('click', function(e) {
  e.preventDefault();
  jQuery('html, body').animate({scrollTop:0}, '300');
});

window.addEventListener('load', (event) => {
    jQuery(".preloader").delay(1000).fadeOut("slow");
});

jQuery(window).scroll(function() {
    var ignite_agency_portfolio_data_sticky = jQuery('.head_bg').attr('data-sticky');

    if (ignite_agency_portfolio_data_sticky == 1) {
      if (jQuery(this).scrollTop() > 1){  
        jQuery('.head_bg').addClass("sticky-head");
      } else {
        jQuery('.head_bg').removeClass("sticky-head");
      }
    }
});

//Preloader
function ignite_agency_portfolio_preloderFunction() {
    setTimeout(function() {           
        document.getElementById("page-top").scrollIntoView();
        
        $('#ctn-preloader').addClass('loaded');  
        // Once the preloader has finished, the scroll appears 
        $('body').removeClass('no-scroll-y');

        if ($('#ctn-preloader').hasClass('loaded')) {
            // It is so that once the preloader is gone, the entire preloader section will removed
            $('#preloader').delay(1000).queue(function() {
                $(this).remove();
                
                // If you want to do something after removing preloader:
                ignite_agency_portfolio_afterLoad();
                
            });
        }
    }, 3000);
}
function ignite_agency_portfolio_afterLoad() {
    // After Load function body!
}