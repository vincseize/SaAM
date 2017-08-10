// CANVAS WIDGETS

// Color chooser widget
var ColorWidget = CanvasWidget.extend({
	color_red: 255,
	color_green: 0,
	color_blue: 0,
	color_alpha: 1,
	colorString: "",
	constructor: function(canvasName, position) {
		this.inherit(canvasName, position);
	},

	drawWidget: function() {
		this.context.clearRect(0,0,255,120);
		var linGradRed = this.context.createLinearGradient(0,0,255,0);
		linGradRed.addColorStop(0, 'rgba(0,'+this.color_green+','+this.color_blue+',1)');
		linGradRed.addColorStop(1, 'rgba(255,'+this.color_green+','+this.color_blue+',1)');

		var linGradGreen = this.context.createLinearGradient(0,0,255,0);
		linGradGreen.addColorStop(0, 'rgba('+this.color_red+',0,'+this.color_blue+',1)');
		linGradGreen.addColorStop(1, 'rgba('+this.color_red+',255,'+this.color_blue+',1)');

		var linGradBlue= this.context.createLinearGradient(0,0,255,0);
		linGradBlue.addColorStop(0, 'rgba('+this.color_red+','+this.color_green+',0,1)');
		linGradBlue.addColorStop(1, 'rgba('+this.color_red+','+this.color_green+',255,1)');

		var linGradAlpha= this.context.createLinearGradient(0,0,255,0);
		linGradAlpha.addColorStop(0, 'rgba('+this.color_red+','+this.color_green+','+this.color_blue+',1)');
		linGradAlpha.addColorStop(1, 'rgba('+this.color_red+','+this.color_green+','+this.color_blue+',0)');

		this.context.fillStyle = linGradRed;
		this.context.fillRect(0,0,255,20);
		this.drawColorWidgetPointer(this.color_red, 20, this.context);

		this.context.fillStyle = linGradGreen;
		this.context.fillRect(0,20,255,20);
		this.drawColorWidgetPointer(this.color_green, 40, this.context);

		this.context.fillStyle = linGradBlue;
		this.context.fillRect(0,40,255,20);
		this.drawColorWidgetPointer(this.color_blue, 60, this.context);

		this.context.fillStyle = linGradAlpha;
		this.context.fillRect(0,60,255,20);
		var alphaPosition = Math.floor((1-this.color_alpha)*255);
		this.drawColorWidgetPointer(alphaPosition, 80, this.context);

		this.context.fillStyle = "black";
		this.context.fillRect(255, 0, 275, 40);

		this.context.fillStyle = "white";
		this.context.fillRect(255, 40, 275, 40);
	},	
		
	drawColorWidgetPointer: function(xPos, yPos) {
		this.context.fillStyle = "white";
		this.context.beginPath();
		this.context.moveTo(xPos - 6, yPos);
		this.context.lineTo(xPos, yPos - 5);
		this.context.lineTo(xPos + 6, yPos);
		this.context.fill();
		
		this.context.strokeWidth = 1;
		this.context.fillStyle = "black";

		this.context.beginPath();
		this.context.arc(xPos, yPos-7.5, 2.5,0,Math.PI*2 ,true);
		this.context.fill();
		this.context.closePath();
	},
	
	checkWidgetEvent: function(e) {
		var mousePos = this.getCanvasMousePos(e);

		if(mousePos.x > 255) {
			if(mousePos.y > 0 && mousePos.y <= 40) {
				this.color_red = 0;
				this.color_green = 0;
				this.color_blue = 0;
			} else {
				this.color_red = 255;
				this.color_green = 255;
				this.color_blue = 255;
			}
		} else {
			if(mousePos.y > 0 && mousePos.y <= 20) {
				this.color_red = mousePos.x;
			} else if(mousePos.y > 20 && mousePos.y <= 40) {
				this.color_green = mousePos.x;
			} else if(mousePos.y > 40 && mousePos.y <= 60) {
				this.color_blue = mousePos.x;
			} else {
				this.color_alpha = 1 - mousePos.x/255;
			}
		}
		
		this.colorString = 'rgba('+this.color_red+','+this.color_green+','+this.color_blue+','+this.color_alpha+')';
		this.drawWidget();
		this.callWidgetListeners();
	}
});


// Line width chooser widget
//
var LineWidthWidget = CanvasWidget.extend({
	lineWidth: null,
	
	constructor: function(canvasName, lineWidth, position) {
		this.lineWidth = lineWidth;
		this.inherit(canvasName, position);
	},

	drawWidget: function() {
		this.context.clearRect(0,0,275,120);
		this.context.beginPath();

		this.context.fillStyle = 'rgba(0,0,0,0.2)';
		this.context.fillRect(0, 0, 275, 76);

		this.context.strokeStyle = 'rgba(255,255,255,1)';
		this.context.moveTo(1, 38);
		this.context.lineTo(274, 38);
		this.context.stroke();

// 		this.context.strokeStyle = 'rgba(255,255,255,0.5)';
// 		this.context.moveTo(1, 19);
// 		this.context.lineTo(274, 19);
// 		this.context.moveTo(1, 57);
// 		this.context.lineTo(274, 57);
// 		this.context.stroke();
		
		this.context.beginPath();
		var linePosition = Math.floor((this.lineWidth*255)/76);
		this.context.fillStyle = 'rgba(255,255,255,1)';
		this.context.arc(linePosition, 38, this.lineWidth/2, 0, Math.PI*2, true);
		this.context.fill();
		this.context.closePath();
	},

	checkWidgetEvent: function(e) {
		var mousePos = this.getCanvasMousePos(e);

		if(mousePos.x >= 0 && mousePos.x <= 255) {
			this.lineWidth = Math.floor(((mousePos.x)*76)/255) + 1;
			this.drawWidget();
			this.callWidgetListeners();
		}
	}
});
