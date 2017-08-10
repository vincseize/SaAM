/************************************************************************
*************************************************************************
@Name :       	QapTcha - jQuery Plugin
@Revison :    	4.1
@Date : 		07/03/2012  - dd/mm/YYYY
@Author:     	 ALPIXEL Agency - (www.myjqueryplugins.com - www.alpixel.fr) 
@License :		 Open Source - MIT License : http://www.opensource.org/licenses/mit-license.php
 
**************************************************************************
*************************************************************************/
jQuery.QapTcha = {
	build : function(options)
	{
        var defaults = {
			txtLock : 'Locked : can\'t submit',
			txtUnlock : 'Unlocked : can submit',
			txtLoss : 'Missing some informations',
			disabledSubmit : true,
			autoRevert : true,
			autoSubmit : false,
			PHPfile : 'actions/qapTcha.php'
        };   
		
		if(this.length>0)
		return jQuery(this).each(function(i) {
			/** Vars **/
			var 
				opts = $.extend(defaults, options),      
				$this = $(this),
				form = $('form').has($this),
				Clr = jQuery('<div>',{'class':'clr'}),
				bgSlider = jQuery('<div>',{'class':'bgSlider'}),
				Slider = jQuery('<div>',{'class':'Slider'}),
				TxtStatus = jQuery('<div>',{'class':' TxtStatus dropError',text:opts.txtLock}),
				inputQapTcha = jQuery('<input>',{'class':'contactField',name:generatePass(32),value:generatePass(7),type:'hidden'});
			
			/** Disabled submit button **/
			if(opts.disabledSubmit) form.find('input[type=\'submit\']').attr('disabled','disabled').addClass('ui-state-disabled');
			
			/** Construct DOM **/
			bgSlider.appendTo($this);
			Clr.insertAfter(bgSlider);
			TxtStatus.insertAfter(Clr);
			inputQapTcha.appendTo($this);
			Slider.appendTo(bgSlider);
			$this.show();
			
			Slider.draggable({ 
				revert: function(){
					if(opts.autoRevert) {
						if(parseInt(Slider.css("left")) > 150) return false;
						else return true;
					}
				},
				containment: bgSlider,
				axis:'x',
				stop: function(event,ui){
					if(ui.position.left > 150) {
						// set the SESSION iQaptcha in PHP file, and check form
						$.post(opts.PHPfile,{
							action : 'qaptcha',
							qaptcha_key : inputQapTcha.attr('name'),
							userMail : $('#contactMailSender').val(),
							userMess : $('#contactMessageSender').val()
						},
						function(data) {
							if(data!=null && !data.error) {
								Slider.draggable('disable').css('cursor','default');
								inputQapTcha.val('');
								TxtStatus.text(opts.txtUnlock).addClass('dropSuccess').removeClass('dropError');
								$('#contactSendBtn').fadeIn(transition/2);
//								form.find('input[type=\'submit\']').removeAttr('disabled').removeClass('ui-state-disabled');
								if(opts.autoSubmit) {
//									form.find('input[type=\'submit\']').trigger('click');
									$('#sendMessageBtn').click();
								}
							}
							else {
								TxtStatus.html(opts.txtLoss+'<br />'+opts.txtLock);
								Slider.css('left', '2px');
							}
						},
						'json');
					}
				}
			});
			
			function generatePass(nb) {
		        var chars = 'azertyupqsdfghjkmwxcvbn23456789AZERTYUPQSDFGHJKMWXCVBN_-#@';
		        var pass = '';
		        for(i=0;i<nb;i++){
		            var wpos = Math.round(Math.random()*chars.length);
		            pass += chars.substring(wpos,wpos+1);
		        }
		        return pass;
		    }
		});
	}
};
jQuery.fn.QapTcha = jQuery.QapTcha.build;