/**
 * Initilize global vars
 */
var isOnimageArr = new Array();
var isOnpanelArr = new Array();
var maincarousel, currentcurousel, currentcontainer = '';
var isDragging = false;

$(document).ready(function() {
	// make jCarousel ready
	$('#mycarousel').jcarousel({
		scroll: 1,
		initCallback: function (carousel) {
			maincarousel = $('#mycarousel');
			currentcurousel = $('#mycarousel');
			currentcontainer = '.contentbox';
		},
		// This tells jCarousel NOT to autobuild prev/next buttons
		buttonNextHTML: null,
		buttonPrevHTML: null
	});
	$('#control-main-right').css({'top': $('.contentarea').position().top+'px'});
	mycarousel_initCallback();
	
	initFancybox ();// initialize fancybox lightbox
	initSubGallery ();// initialize sub gallery
	
	// bind mouseup event with sub gallery so it can be closed when not draggin the gallery strip
	$('.subgalpopup').bind('mouseup',function() {
		if (!isDragging) {
			currentcurousel = maincarousel;
			currentcontainer = '.contentbox';
			$(this).css({'display':'none'});
			$('.subgal-wrapper-inner').css({'display':'none'});
		}
	});
	
	// show info in lightbox
	$("#infolink").fancybox({
		'margin' : 0,
		'width': 850,
		'height' : 440,
		'autoDimensions': false,
		'autoScale' : false,
		'hideOnContentClick': false
	});
	// show contact in lightbox
	$("#contactlink").fancybox({
		'margin' : 0,
		'width': 450,
		'height' : 235,
		'autoDimensions': false,
		'autoScale' : false,
		'hideOnContentClick': false
	});
	
	enableImageHover();
});


// Function for enabling all anchors as lightbox link if attribute "rel" is set as lightbox.
function initFancybox () {
	var boxes = $('a');
	
	for (var i=0;i<boxes.length;i++) {
		if($(boxes[i]).attr('rel') == 'lightbox') {
			$(boxes[i]).fancybox({'hideOnContentClick': false});
		}
	}
}

// Function for initializing sub gallery
function initSubGallery () {
	var boxes = $('img');
	
	// loop through all images which has attribute "rel" as subgal, so clicking on it can invoke subgallery
	for (var i=0;i<boxes.length;i++) {
		if($(boxes[i]).attr('rel') == 'subgal') {
			// bind the image with sub-gallery with mouse up event, and ignore our process if user's dragging the gallery
			$(boxes[i]).bind('mouseup',function(){
				if (!isDragging) {
					currentcurousel = '#subgal-'+$(this).attr('pgal');
					currentcontainer = '#gal-wrappersg-'+$(this).attr('pgal');
					
					$('.subgalpopup').css({'display':'block'});
					$('#gal-wrappersg-'+$(this).attr('pgal')).css({'display':'block'});
					// initiate sub-gallery carousel
					$('#subgal-'+$(this).attr('pgal')).jcarousel({
						scroll: 1,
						// This tells jCarousel NOT to autobuild prev/next buttons
						buttonNextHTML: null,
						buttonPrevHTML: null 
					});
					
					// also make sub-gallery draggable
					$('#subgal-'+$(this).attr('pgal')).draggable({
						axis: "x",
						start: function (event) {
							isDragging = true;
							},
						stop: function (event, ui) {
							carouselDrag(event, ui);
							}
					});
				}
			});
		}
	}
}

// function for AJAX call
function loadAJAX (params, data, target, callfunc,args) {
	var tags = params.split(',');
	var pdata = data.split(',');
	
	callAJAX(tags,pdata,target,callfunc,args);
}

// AJAX function
function callAJAX(tags, data, target, callfunc,args) {
	var postdata = "";
	callfunc = (typeof callfunc == 'undefined')?'':callfunc;
	
	// loop through the POST data and embed them
	for (i = 0; i < tags.length; i++) {
		if (i == 0) {
			postdata = tags[i]+"="+data[i];
		} else {
			postdata += "&"+tags[i]+"="+data[i];
		}
	}
	
	// jQuery AJAX call
	new $.ajax({
				type:'POST',
				url:'../library/ajax.php',
				data:postdata,
				success:function(transport){		
					$(target).html(transport);// update the target element with AJAX response
					if (callfunc != '') { // if callback supplied call it
						window[callfunc](args);
						initDatePicker();
					}
				}});
}

// carousel scroll animation (show next elements)
function animateNext() {
	var containerWidth = $(currentcontainer).outerWidth();
	var sliderLeft = $(currentcurousel).position().left;
	var sliderWidth = $(currentcurousel).outerWidth();
	var totalTravelLength =  containerWidth - sliderWidth;
	var lengthToTravel = sliderWidth - (containerWidth + sliderLeft);
	var scrollSpeed = lengthToTravel * 3;
	
	if (lengthToTravel > 30) {
		$(currentcurousel).animate({
			left: totalTravelLength + 'px'
		},'slow','linear');
	}
}

// carousel scroll animation (show previous elements)
function animatePrevious() {
	var lengthToTravel = $(currentcurousel).position().left;
	var scrollSpeed = (lengthToTravel < 0)?(lengthToTravel * -1) * 3:lengthToTravel * 3;
	
	$(currentcurousel).animate({
		left: '0px'
	},'slow','linear');
}

// carousel scroll animation (while dragging the strip)
function carouselDrag(edata, uidata) {
	var containerWidth = $(currentcontainer).outerWidth();
	var sliderWidth = $(currentcurousel).outerWidth();
	var totalTravelLength =  containerWidth - sliderWidth;
	
	// if we hit the margin, stick the strip to it
	if (totalTravelLength > uidata.position.left) {
		$(currentcurousel).css({
			'left': (totalTravelLength > 0)?'0px':totalTravelLength + 'px'
		});
	} else if (uidata.position.left > 0) {
		$(currentcurousel).css({
			'left': '0px'
		});
	}
	
	isDragging = false;
}

// after initializing the carousel call this function
function mycarousel_initCallback() {
	// bind mouse events to previous button (hover)
	$('#control-main-left').bind('mouseenter',function(){
		//currentcurousel.prev();
		//carousel.prev();
		animatePrevious();
		return false;
	}).bind('mouseout',function(){
		$(currentcurousel).stop(true);
	});
	// bind mouse events to next button (hover)
	$('#control-main-right').bind('mouseenter',function(){
		//currentcurousel.next();
		//carousel.next();
		animateNext();
		return false;
	}).bind('mouseout',function(){
		$(currentcurousel).stop(true);
	});
	
	// initiate the strip as draggable (restricting dragging horizontally only)
	$('#mycarousel').draggable({
		axis: "x",
		start: function (event) {
			isDragging = true;
			},
		stop: function (event, ui) {
			carouselDrag(event, ui);
			}
	});
}

// add some hover effect to images
function enableImageHover() {
	// if hovering through carousel images set appropriate class to image title span
	$('img').bind('mouseover', function() {
		var imgid = $(this).attr('nodeid');
		
		$('.carnode'+imgid+' span').addClass('galhover');
	});
	$('img').bind('mouseout', function() {
		var imgid = $(this).attr('nodeid');
		
		$('.carnode'+imgid+' span').removeClass('galhover');
	});
}