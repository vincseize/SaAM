var year = new Date().getFullYear();
var month = new Date().getMonth();
var day = new Date().getDate();

var colorsByProj = {};
var colorsDispos = ['f726d2', '26b3f7', '26f788', 'a14856', 'ffa63a'];

$(function(){
	$('#calendarNav').show();

	var stageHeight = $('#stage').height() - 25;
	var $calendar = $('#calendar');

	$.each(projectShowed, function(ix,pId){
		colorsByProj[pId] = colorsDispos[ix];
	});
	console.log(colorsByProj);

	$calendar.weekCalendar({
		data: calendarDataFile,
		date: new Date(),
		timeFormat : "H:i",
		dateFormat : "d M Y",
		use24Hour : true,
		daysToShow : 7,
		firstDayOfWeek : 1, // 0 = Dimanche, 1 = Lundi, ...
		useShortDayNames: false,
		timeSeparator : "-",
		startParam : "début",
		endParam : "fin",
		businessHours : {start: 8, end: 20, limitDisplay : true},
		newEventText : "Nouvel évènement",
		timeslotHeight: (stageHeight/12/2.15),
		defaultEventLength : 2,
		timeslotsPerHour : 2,
		buttons : false,
		buttonText : {
			today : "today",
			lastWeek : "&nbsp;&lt;&nbsp;",
			nextWeek : "&nbsp;&gt;&nbsp;"
		},
		scrollToHourMillis : 5000,
		allowCalEventOverlap : true,
		overlapEventsSeparate: true,
		readonly: false,
		height: function(){return stageHeight;},
		draggable : function(calEvent, element) {
			if(calEvent.end.getTime() < new Date().getTime())
				calEvent.readOnly = true;
			return calEvent.readOnly != true;
		},
		resizable : function(calEvent, element) {
			if(calEvent.end.getTime() < new Date().getTime())
				calEvent.readOnly = true;
			return calEvent.readOnly != true;
		},
		eventClick : function(calEvent, element) {
			if (calEvent.status > userStatus) return;
			var $dialogContent = $("#modal_eventEdit");
			resetForm($dialogContent);
			var startField = $dialogContent.find("select[name='start']").val(calEvent.start);
			var endField = $dialogContent.find("select[name='end']").val(calEvent.end);
			var projectField = $dialogContent.find("#projSelect").val(calEvent.project);
			var titleField = $dialogContent.find("input[name='title']").val(calEvent.title);
			var bodyField = $dialogContent.find("textarea[name='body']");
			bodyField.val(calEvent.body);

			$dialogContent.dialog({
				modal: false,
				title: "Modifier - " + calEvent.title,
				width: 550,
				close: function() {
					$dialogContent.dialog("destroy");
					$dialogContent.hide();
					$('#calendar').weekCalendar("removeUnsavedEvents");
				},
				buttons: {
					save : function() {
						calEvent.start = new Date(startField.val());
						calEvent.end = new Date(endField.val());
						calEvent.project = projectField.val();
						calEvent.title = titleField.val();
						calEvent.body = bodyField.val();

						if (saveEventJson('modif', calEvent))
							$calendar.weekCalendar("updateEvent", calEvent);
						$dialogContent.dialog("close");
					},
					"delete" : function() {
						if (saveEventJson('delete', calEvent))
							$calendar.weekCalendar("removeEvent", calEvent.id);
						$dialogContent.dialog("close");
					},
					cancel : function() {
						$dialogContent.dialog("close");
					}
				}
			}).show();

			$dialogContent.find(".date_holder").text($calendar.weekCalendar("formatDate", calEvent.start, 'D, d/m/Y'));
			setupStartAndEndTimeFields(startField, endField, calEvent, $calendar.weekCalendar("getTimeslotTimes", calEvent.start));
			$(window).resize().resize(); //fixes a bug in modal overlay size ??

		},
		eventNew : function(calEvent, element) {
			if(calEvent.end.getTime() < new Date().getTime()) {
				$('#calendar').weekCalendar("removeUnsavedEvents");
				return;
			}
			var day = calEvent.start.getDate();
			calEvent.end.setDate(day);
			var $dialogContent = $("#modal_eventEdit");
			resetForm($dialogContent);
			var startField = $dialogContent.find("select[name='start']").val(calEvent.start);
			var endField = $dialogContent.find("select[name='end']").val(calEvent.end);
			var projectField = $dialogContent.find("select[name='project']");
			var titleField = $dialogContent.find("input[name='title']");
			var bodyField = $dialogContent.find("textarea[name='body']");

			$dialogContent.dialog({
				modal: false,
				title: "Nouvel évènement",
				width: 550,
				close: function() {
					$dialogContent.dialog("destroy").hide();
					$('#calendar').weekCalendar("removeUnsavedEvents");
				},
				buttons: {
					save : function() {
						calEvent.id = idLastEvent;
						idLastEvent++;
						calEvent.status = userStatus;
						calEvent.start = new Date(startField.val());
						calEvent.end = new Date(endField.val());
						calEvent.project = projectField.val();
						calEvent.title = titleField.val();
						calEvent.body = bodyField.val();

						$calendar.weekCalendar("removeUnsavedEvents");
						if (saveEventJson('nouveau', calEvent))
							$calendar.weekCalendar("updateEvent", calEvent);
						$dialogContent.dialog("close");
					},
					cancel : function() {
						$dialogContent.dialog("close");
					}
				}
			}).show();

			$dialogContent.find(".date_holder").text($calendar.weekCalendar("formatDate", calEvent.start, 'D, d/m/Y'));
			setupStartAndEndTimeFields(startField, endField, calEvent, $calendar.weekCalendar("getTimeslotTimes", calEvent.start));
		},
		eventRender : function(calEvent, element) {
			if(calEvent.end.getTime() < new Date().getTime())
				element.css("background-color", "#666").find(".wc-time").css({"background-color": "#777", "border":"1px solid #"+colorsByProj[calEvent.project]});
			else {
				element.css({"background-color":"#003147", "border-bottom":"1px solid #"+colorsByProj[calEvent.project]})
				.find(".wc-time").addClass('ui-state-focus').css("border","1px solid #"+colorsByProj[calEvent.project]);
			}
		},
		eventAfterRender : function(calEvent, element) {
			if (!in_array(calEvent.project, projectShowed)) element.remove();
		},
		eventDrag : function(calEvent, element) {return true;},
		eventDrop : function(calEvent, element) {
			var minutes = (calEvent.end.getMinutes()<15 || calEvent.end.getMinutes()>45) ? 0 : 30;
			var hours = (calEvent.end.getMinutes()>45) ? calEvent.end.getHours()+1 : calEvent.end.getHours();
			calEvent.end.setHours(hours, minutes, 0, 0);
			if (saveEventJson('modif', calEvent)) return true;
			else return false;
		},
		eventResize : function(calEvent, element) {
			var minutes = (calEvent.end.getMinutes()<15 || calEvent.end.getMinutes()>45) ? 0 : 30;
			calEvent.end.setMinutes(minutes, 0, 0);
			if (saveEventJson('modif', calEvent)) return true;
			else return false;
		},
		eventMouseover : function(calEvent, $event) {return true;},
		eventMouseout : function(calEvent, $event) {return true;},
		calendarBeforeLoad : function(calendar) {return true;},
		calendarAfterLoad : function(calendar) {return true;},
		noEvents : function() {return true;},
		shortMonths : ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'],
		longMonths : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
		shortDays : ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
		longDays : ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'jeudi', 'Vendredi', 'Samedi']
	});

	var $endTimeField = $("select[name='end']");
	var $endTimeOptions = $endTimeField.find("option");

	//reduces the end time options to be only after the start time options.
	$("select[name='start']").change(function() {
		var startTime = $(this).find(":selected").val();
		var currentEndTime = $endTimeField.find("option:selected").val();
		$endTimeField.html(
			$endTimeOptions.filter(function() {
				return startTime < $(this).val();
			})
		);

		var endTimeSelected = false;
		$endTimeField.find("option").each(function() {
			if ($(this).val() === currentEndTime) {
				$(this).attr("selected", "selected");
				endTimeSelected = true;
				return false;
			}
		});

		if (!endTimeSelected) {		//automatically select an end date 2 slots away.
			$endTimeField.find("option:eq(1)").attr("selected", "selected");
		}
	});

	// Navigation dans le calendrier
	$('.headerStage').off('click', '#calendarPrevWeek');
	$('.headerStage').on('click', '#calendarPrevWeek', function(){
		$('#calendar').weekCalendar("prev");
	});
	$('.headerStage').off('click', '#calendarToday');
	$('.headerStage').on('click', '#calendarToday', function(){
		$('#calendar').weekCalendar("today");
	});
	$('.headerStage').off('click', '#calendarNextWeek');
	$('.headerStage').on('click', '#calendarNextWeek', function(){
		$('#calendar').weekCalendar("next");
	});
	// Cacher le message de retour Ajax en double cliquant dessus
	$('.pageContent').on('dblclick', "#retourAjax", function(){
		$(this).fadeOut();
	});

	// Sauvegarde un évènement dans le fichier json
	function saveEventJson(type, calEvent) {
		if (calEvent == null || calEvent == undefined) { alert('une erreur est survenue... merci de recommencer !'); return false;}
		var strAjax = "action=save&type="+type+"&event="+encodeURIComponent(JSON.encode(calEvent));
		AjaxJson(strAjax, 'home_agenda', displayMessage);
		return true;
	}

	// Réinitialise le formulaire de modif/ajout d'évènement
	function resetForm($dialogContent) {
		$dialogContent.find("input").val("");
		$dialogContent.find("textarea").val("");
	}

	// Sets up the start and end time fields in the calendar event form for editing based on the calendar event being edited
	function setupStartAndEndTimeFields($startTimeField, $endTimeField, calEvent, timeslotTimes) {
		for (var i = 0; i < timeslotTimes.length; i++) {
			var startTime = timeslotTimes[i].start;
			var endTime = timeslotTimes[i].end;
			var startSelected = "";
			if (startTime.getTime() === calEvent.start.getTime())
				startSelected = "selected=\"selected\"";
			var endSelected = "";
			if (endTime.getTime() === calEvent.end.getTime())
				endSelected = "selected=\"selected\"";
			$startTimeField.append("<option value=\"" + startTime + "\" " + startSelected + ">" + timeslotTimes[i].startFormatted + "</option>");
			$endTimeField.append("<option value=\"" + endTime + "\" " + endSelected + ">" + timeslotTimes[i].endFormatted + "</option>");

		}
		$endTimeOptions = $endTimeField.find("option");
		$startTimeField.trigger("change");
	}

});

// Affiche un message tout en haut (retour ajax)
function displayMessage (retour) {
	$("#retourAjax").html(retour.message).fadeIn();
	if (retour.error == 'ERROR') {
		$("#retourAjax").addClass('ui-state-error');
		return;
	}
	setTimeout(function(){$("#retourAjax").fadeOut();}, 2000);
}
