// CANVAS WIDGETS : MAIN

//
var CanvasWidget = Base.extend({
	canvas: null,
	context: null,
	position: null,
	widgetListeners: null,

	/**
	 * constuctor
	 * 
	 * @param {String} canvasName - the id of the corresponding canvas html element
	 * @param {Array} position - the absolute position of the canvas html elemnt, {x:#,y:#}
	 */
	constructor: function(canvasElementID, position) {
		this.canvas = document.getElementById(canvasElementID);
		if (this.canvas)
			this.context = this.canvas.getContext('2d');
		this.drawWidget();
		this.initMouseListeners();
		this.position = position;
		this.widgetListeners = new Array();
	},

	/**
	 * Initializes all the mouse listeners for the widget.
	 */
	initMouseListeners: function() {
		this.mouseMoveTrigger = new Function();
		if (!this.canvas) return;
		if (document.all) {
			this.canvas.attachEvent("onmousedown", this.mouseDownActionPerformed.bindAsEventListener(this));
			this.canvas.attachEvent("onmousemove", this.mouseMoveActionPerformed.bindAsEventListener(this));
			this.canvas.attachEvent("onmouseup", this.mouseUpActionPerformed.bindAsEventListener(this));
			this.canvas.attachEvent("onmouseout", this.mouseUpActionPerformed.bindAsEventListener(this));
		} else {
			this.canvas.addEventListener("mousedown", this.mouseDownActionPerformed.bindAsEventListener(this), false);
			this.canvas.addEventListener("mousemove", this.mouseMoveActionPerformed.bindAsEventListener(this), false);
			this.canvas.addEventListener("mouseup", this.mouseUpActionPerformed.bindAsEventListener(this), false);
			this.canvas.addEventListener("mouseout", this.mouseUpActionPerformed.bindAsEventListener(this), false);
		}
	},

	/**
	 * Triggered by any mousedown event on the widget. This function calls 
	 * checkWidgetMouseEvent() and links the mousemove listener to checkWidgetEvent().
	 *
	 * Override this function if you want direct access to mousedown events.
	 *
	 * @param {Event} e
	*/
	mouseDownActionPerformed: function(e) {
		this.mouseMoveTrigger = function(e) {
			this.checkWidgetEvent(e);
		}
		this.checkWidgetEvent(e);
	},
	
	/**
	 * Triggered by any mousemove event on the widget. 
	 *
	 * Override this function if you want direct access to mousemove events.
	 *
	 * @param {Event} e
	*/
	mouseMoveActionPerformed: function(e) {
		this.mouseMoveTrigger(e);
	},
	
	/**
	 * Triggered by any mouseup or mouseout event on the widget. 
	 *
	 * Override this function if you want direct access to mouseup events.
	 *
	 * @param {Event} e
	*/
	mouseUpActionPerformed: function(e) {
		this.mouseMoveTrigger = new Function();
	},

	/**
	 * Called by the mousedown and mousemove event listeners by default.
	 *
	 * This function must be implemented by any class extending CPWidget.
	 *
	 * @param {Event} e
	*/
	checkWidgetMouseEvent: function(e) {},
	
	/**
	 * Draws the widget.
	 *
	 * This function must be implemented by any class extending CPWidget.
	 *
	*/
	drawWidget: function() {},

	/**
	 * Used to add event listeners directly to the widget.  Listeners registered 
	 * with this function are triggered every time the widget's state changes.
	 *
	 * @param {Function} eventListener
	*/
	addWidgetListener: function(eventListener) {
		this.widgetListeners[this.widgetListeners.length] = eventListener;
	},
	
	/**
	 * Executs all functions registered as widgetListeners.  Should be called every time 
	 * the widget's state changes.
	*/
	callWidgetListeners: function() {
		if(this.widgetListeners.length != 0) {
			for(var i=0; i < this.widgetListeners.length; i++) 
				this.widgetListeners[i]();
		}
	},
	
	/**
	 * Helper function to get the mouse position relative to the canvas position.
	 *
	 * @param {Event} e
	*/
	getCanvasMousePos: function(e) {
		return {x: e.clientX - this.position.x, y: e.clientY - this.position.y};
	}

});

var CanvasHelper = {
	canvasExists: function(canvasName) {
		var canvas = document.getElementById(canvasName);
		return canvas.getContext('2d');
	}
}