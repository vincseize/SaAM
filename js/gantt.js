
var t_o;

// Document ready
$(function(){

	$('#schedule').draggable({
		handle:		'#calTimeline',
		axis:		'x',
		disabled:	true
	}).css('height', '100%');

	var widthDays = $('.daysCol').width();
	var widthMois = $('.moisCol').width();
	if (widthMois < 32) $('.moisCol').hide();
	if (widthDays < 16) $('.daysCol').hide();
	refreshDatesEventShow();

	var zoomLevel = 1;
	$("#zoomTimeIn").click(function(){
		resizeTimeline(1);
	});
	$("#zoomTimeOut").click(function(){
		resizeTimeline(-1);
	});
	$("#zoomTimeAll").click(function(){
		resetTimeline();
	});

	function resizeTimeline(inc) {
		zoomLevel += inc;
		if (zoomLevel > 20) { zoomLevel = 20; return; }
		if (zoomLevel < 2)  { zoomLevel = 1;  resetTimeline(); return; }
		var newWidth = ($('.pageContent').width() * zoomLevel) / 2;
		var newPos   = $("#schedule").position();
		var newOffsetL;
		var offsetFac = (Math.abs(newPos.left) / zoomLevel );
		if (newPos.left > 0 )
			newOffsetL = newPos.left + (offsetFac * inc);
		if (newPos.left < 0)
			newOffsetL = newPos.left - (offsetFac * inc);
		$('#calTimeline, #calContent').animate({left: '-'+newWidth+'px', right: '-'+newWidth+'px'}, transition);
		$("#schedule").animate({left: newOffsetL+'px'}, transition);
		clearTimeout(t_o);
		t_o = setTimeout(function(){
			$( "#schedule" ).draggable("enable");
			refreshMoisDays();
		}, transition);
	}

	function resetTimeline() {
		$( "#schedule" ).animate({left:'0px'}, transition);
		$('#calTimeline, #calContent').animate({left: '0px', right: '0px'}, transition);
		clearTimeout(t_o);
		t_o = setTimeout(function(){
			$( "#schedule" ).draggable("disable").removeClass('ui-state-disabled');
			refreshMoisDays();
		}, transition);
	}

	function refreshMoisDays() {
		widthDays = $('.daysCol').width();
		widthMois = $('.moisCol').width();
		if (widthMois < 32) $('.moisCol').hide();
		else $('.moisCol').show();
		if (widthDays < 16) $('.daysCol').hide();
		else $('.daysCol').show();
		refreshDatesEventShow();
	}
});
// FIN document ready


function refreshDatesEventShow() {
	$('.calLine').each(function(){
		var eventWidth = $(this).width();
		if (eventWidth > 200) $(this).find('.datesEvent').removeClass('hide');
		else $(this).find('.datesEvent').addClass('hide');
	});
}
