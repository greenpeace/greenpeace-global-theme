$ = jQuery;

$(function() {
	$('li.article-mobile-view').css('cursor', 'pointer').click(function() {
		window.location.href = "#";
		return false;
	});
});
$(document).ready(function() {
	$(".step-info-wrap").click(function(){
		if($(this).parent().hasClass('active')){
			$(this).parent().removeClass('active');
		}
		else {
			$('.col').removeClass('active');
			$(this).parent().addClass('active');
		}
	});

	// Submit form on Sort change event.
	$("#select_order").on("change", function () {
		$("#orderby", $("#search_form")).val( $(this).val() ).parent().submit();
		return false;
	});

	// Add click event for load more button in content four column block
	$(".content-four-column-load-btn").off("click").on("click", function () {
		var content_four_column_row = '.c4-row-hidden';
		$(content_four_column_row).first().css('display', '').removeClass('c4-row-hidden');
		if ($(content_four_column_row).size() === 0) {
			$('.load-more-button-div').css('display', 'none');
		}
	});
});

$('.country-select-dropdown').click(function(){
	$(this).parent().toggleClass('active-li');
	$('.country-select-box').toggle();
});

$('.country-select-box .country-list li').click(function(){
	$(this).parents('.country-select-box').find('li').removeClass('active');
	$(this).addClass('active');
});

// Footer JS goes in this
// Header JS goes in this.

// Hide Header on on scroll down
if($( window ).width() <= 768) {
	var didScroll;
	var lastScrollTop = 0;
	var delta = 5;
	var navbarHeight = $('.top-navigation').outerHeight();
	$(window).scroll(function(event){
		didScroll = true;
	});
	setInterval(function() {
		if (didScroll) {
			hasScrolled();
			didScroll = false;
		}
	}, 250);
	function hasScrolled() {
		var st = $(this).scrollTop();
		if(Math.abs(lastScrollTop - st) <= delta)
			return;
		if (st > lastScrollTop && st > navbarHeight){
			$('.top-navigation').removeClass('nav-down').addClass('nav-up');
		} else {
			if(st + $(window).height() < $(document).height()) {
				$('.top-navigation').removeClass('nav-up').addClass('nav-down');
			}
		}
		lastScrollTop = st;
	}
	var $slider = $('.mobile-menus');
	$(document).click(function() {
		if($('.menu').hasClass('active')){
			//Hide the menus if visible
			$slider.animate({
				left: parseInt($slider.css('left'),10) == 0 ?
					-320 : 0
			});
			$('.menu').removeClass('active');
		}
		if($('.search-box').hasClass('active')){
			//Hide the search if visible
			$searchBox.slideToggle().toggleClass('active');;
		}
	});

	$('.menu').click(function() {
		event.stopPropagation();
		$(this).toggleClass('active');
		$slider.animate({
			left: parseInt($slider.css('left'),10) == -320 ?
				0 : -320
		});
	});

	var $searchBox = $('#search .search-box');
	var $searchTrigger = $('#search-trigger');

	$searchTrigger.on('click', function(e) {
		event.stopPropagation();
		$searchBox.slideToggle().toggleClass('active');
	});
};
$(function() {
	$('#search-type button').click(function() {
		$('#search-type button').removeClass("active");
		$(this).addClass("active");
	});

	$('.btn-filter').click(function() {
		$('#filtermodal').modal('show');
	});
});
// First Index
currentIndex = $('.carousel-item.active').next('.carousel-item').find('img').attr('src');

$('#carousel-wrapper').on('slid.bs.carousel', function () {
	currentIndex = $('.carousel-item.active').next('.carousel-item');
	var e = currentIndex.find('img').attr('src');
	// Last Index
	if(e === 'undefined' || e === undefined) {
		currentIndex = $('.carousel-item').first('.carousel-item').find('img').attr('src');
	} else {
		currentIndex = currentIndex.find('img').attr('src');
	}
	$('a.carousel-control-next').css('background-image', 'url(' + currentIndex + ')');

});

$('a.carousel-control-next').css('background-image', 'url(' + currentIndex + ')');
