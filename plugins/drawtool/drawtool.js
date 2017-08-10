var canvasPainter;
var colorWidget;
var lineWidthWidget;
var drawing;

// Document ready
$(function(){

	if ((winWidth-90-200) < Wmax || (winHeight-50) < Hmax){
		$('#drawToolContainer').html("");
		$('#msgSave').html(
			"<h3>Drawtool info:</h3>"+
			"Your screen resolution is <b>too small</b> for Drawtool plugin to works well."+
			"<p>Hit <b>ESCAPE</b> key to go back.</p>"
		).removeClass('ui-state-default').addClass('ui-state-error').show();
		return;
	}

	if (CanvasHelper.canvasExists("canvasDrawtool")) {
		canvasPainter = new CanvasPainter("canvasDrawtool", "canvasInterface", {x: 90+35, y: 10+35});
		// Set defaults : color, width, brush
		canvasPainter.setColor('rgba(255,0,0,1)');
		canvasPainter.setLineWidth(8);
		canvasPainter.setDrawAction(1);
		// Init color widget
		colorWidget = new ColorWidget('colorChooser', {x: chooserWidgets_left+35, y: 10+35});
		colorWidget.addWidgetListener(function() {
			canvasPainter.setColor(colorWidget.colorString);
		});
		// Init line width widget
		lineWidthWidget = new LineWidthWidget('lineWidthChooser', 8, {x: chooserWidgets_left+35, y: 120+35});
		lineWidthWidget.addWidgetListener(function() {
			canvasPainter.setLineWidth(lineWidthWidget.lineWidth);
		});
		// Init drawing history
		drawing = new CPDrawing(canvasPainter);
	}

	$('.brushBtn').click(function () {
		var brush = parseInt($(this).attr('brushNo'));
		$('.ctrl_btn').removeClass("selected");
		$('.ctrl_btn[brushNo='+brush+']').addClass("selected");
		canvasPainter.setDrawAction(brush);
	});
});
// FIN document ready

function undoDraw() {
	drawing.removeLastNode();
}
function redoDraw() {
	drawing.addLastRemovedNode();
}
function clearDraw() {
	canvasPainter.setDrawAction(5);
}

function saveCanvas() {
	var comment = $('#addMessageFromDraw textarea').val();
	var msgComment = (comment.length > 3) ? " and comment" : "";
	if (!confirm("Save this drawing"+msgComment+", and replace last published?\n\nThe last published will move to 'work in progress' folder. Please confirm."))
		return;
	$('#msgSave').html('Saving drawing'+msgComment+'...<br /><img src="gfx/ajax-loader-big.gif" />').addClass('ui-state-default').show();
	var canvasDrawtool = document.getElementById("canvasDrawtool");
	var canvasData = canvasDrawtool.toDataURL("image/png");
	var dataPost = {
		action: 'saveDrawing',
		pubFile: pubFile,
		idProj: idProj,
		dept: id_dept,
		dataFond: dataFond,
		dataDraw: encodeURIComponent(canvasData)
	};
	if (typeof shot_ID !== "undefined") dataPost['shotID'] = shot_ID;
	if (typeof sceneID !== "undefined") dataPost['sceneID'] = sceneID;
	if (typeof nameAsset !== "undefined"){
		dataPost['idAsset'] = idAsset;
		dataPost['pathAsset'] = pathAsset;
		dataPost['nameAsset'] = nameAsset;
	}
	if (comment.length > 3)
		dataPost['addMessage'] = comment;
	$.post('plugins/drawtool/saveDrawing.php', dataPost, function(retour){
		console.log(retour);
		if (retour.error == "OK") {
			$('#msgSave').html(retour.message+'<p class="petit">Please wait for reloading...</p>').removeClass('ui-state-default').addClass('ui-state-highlight');
			$('.deptBtn[active]').click();
			$.fancybox.close(true);
		}
		else {
			$('#msgSave').html(retour.message).removeClass('ui-state-default').addClass('ui-state-error');
			setTimeout(function(){ $('#msgSave').fadeOut(2000); }, 3000);
		}
	}, 'json');
}

function closeDrawtool () {
	$.fancybox.close(true);
}