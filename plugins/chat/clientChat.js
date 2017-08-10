
// globales
var lastUsrCausant = 0;
var lastMessageChat = '';
var lastMessageTime = 0;
var privateMsg = false;
var openedChat	= [];
var authChatRoom = false;
var countAttempts = 1;
var maxAttempts	  = 5;

$(function(){
	connectToChat();
});

function connectToChat () {
	$('#avatarsList').html('<span class="ui-state-disabled">Connecting to chat...</span>');
	$.ajax({
		url: socketURL+'/socket.io/socket.io.js',
		dataType: 'script',
		timeout: chatTimeout * 1000,
		success: function() { runTchat(); },
		error: function(e) {
//			console.log('SERVER NodeJS not running! ('+e.statusText+') Please check your firewall settings. (attempt '+countAttempts+'/'+maxAttempts+')');
			var chatLog = 'Chat server is down';
			if (countAttempts < maxAttempts) {
				chatLog += ', retrying...';
				setTimeout(connectToChat, countAttempts * 1000);
				countAttempts += 1;
			}
			else
				chatLog += '. Aborted.';
			$('#avatarsList').html('<span class="ui-state-disabled doigt" title="Please check your firewall settings for: '+socketURL+'. Then, click here to retry!" onclick="countAttempts=1; connectToChat()">'+chatLog+'</span>');
		}
	});
}


function runTchat() {
	// INIT du socket
	nodeJSrunning = true;
	var socket = io.connect(socketURL);

	// Quand user actuel se connecte
	socket.on('conxOK', function(usrList) {
//		console.log('Current user connected to chat, with '+(usrList.length-1)+' other people!');
//		console.log(usrList);
		refreshAvatarList(usrList);
	});

	// Quand erreur lors de l'init connexion
	socket.on('badConx', function(err){
//		console.log("Can't auth user to chat!");
//		console.log(err.errMsg);
		$('#avatarsList').html('<span class="ui-state-disabled" title="'+err.errMsg+'">.</span>');
	});

	// Quand quelqu'un d'autre se connecte
	socket.on('newConx', function(usrList){
//		console.log('New user connected to chat!');
//		console.log(usrList);
		refreshAvatarList(usrList);
	});

	// Quand quelqu'un se déconnecte
	socket.on('delConx', function(usrList){
//		console.log('A user disconnected from chat!');
//		console.log(usrList);
		refreshAvatarList(usrList);
	});

	// Click sur un user dans la liste pour restreindre à celui-ci (private msg)
	$('#avatarsList').off('click', '.avatarDiv');
	$('#avatarsList').on('click', '.avatarDiv', function(){
		var poteID	  = $(this).attr('idUser');
		var poteLogin = $(this).attr('pseudoUser');
		var avatar	  = $(this).children('img').attr('src');
		if ($(this).attr('idUser') == userID)
			return;
		if (openedChat.indexOf(poteID) > -1) {
			$('.chatbox[idCorresp="'+poteID+'"]').find('.chatboxtextarea').focus().addClass('chatboxtextareaselected');
			return;
		}
		// Génération de la boite de chat
		var classHead  = (poteID == '999999') ? 'headEverybody' : '';
		var classTitle = (poteID == '999999') ? '' : 'ui-state-disabled';
		avatar = (poteID == '999999') ? '" alt=" "' : avatar;
		var $chatBox = $('<div class="chatbox" idCorresp="'+poteID+'">'
						+'<div class="chatboxhead '+classHead+' ui-corner-top">'
							+'<div class="inline chatboxtitle '+classTitle+'">'
								+'<div class="inline mid"><img src="'+avatar+'" /></div> '
								+'<div class="inline mid">'+poteLogin+'</div>'
							+'</div>'
							+'<div class="chatboxoptions">'
								+'<span class="chatBoxReduce" title="reduce chat window">-</span>&nbsp;'
								+'<span class="chatBoxClose petit" title="close chat window">X</span>'
							+'</div>'
							+'<br clear="all"/>'
						+'</div>'
						+'<div class="chatboxcontent"></div>'
						+'<div class="chatboxinput ui-corner-bottom">'
							+'<textarea class="chatboxtextarea"></textarea>'
						+'</div>'
					+'</div>');
		$chatBox.draggable({handle: '.chatboxhead'});
		$chatBox.find('.chatboxcontent').slimscroll({
			position: 'right',
			height: '200px',
			size: '10px',
			wheelStep: 7,
			railVisible: true
		}).parent('.slimScrollDiv').height(206).css('box-shadow', '-5px 0 5px -5px #000, 5px 0 5px -5px #000');
		$chatBox.css({position: 'absolute', right: ((openedChat.length * 230)+15)+'px'});

		// Intégration de la boite de chat dans le body
		socket.emit('openChatWindow', {from: userID, to: poteID});
		$chatBox.appendTo('body').show(transition/2).find('.chatboxtextarea').addClass('chatboxtextareaselected').focus();
		openedChat.push(poteID);

		// Bouton fermer boite de chat
		$chatBox.off('click', '.chatBoxClose');
		$chatBox.on('click', '.chatBoxClose', function(){
			socket.emit('closeChatWindow', {from: userID, to: poteID});
			openedChat.splice(openedChat.indexOf(poteID), 1);
			$chatBox.hide(transition/2, function(){ $(this).remove(); });
		});

		// Bouton reduction boite de chat
		$chatBox.off('click', '.chatBoxReduce');
		$chatBox.on('click', '.chatBoxReduce', function(){
			if ($(this).attr('reduce') == 'yes') {
				$chatBox.find('.slimScrollDiv').animate({height:200}, transition);
				$chatBox.find('.chatboxinput').show();
				$(this).attr('reduce', 'no');
			}
			else {
				$chatBox.find('.chatboxinput').hide();
				$chatBox.find('.slimScrollDiv').animate({height:0}, transition);
				$(this).attr('reduce', 'yes');
			}
		});

		// Focus texte boite de chat
		$chatBox.off('focus', '.chatboxtextarea');
		$chatBox.on('focus', '.chatboxtextarea', function(){
			$(this).addClass('chatboxtextareaselected');
		}).on('blur', '.chatboxtextarea', function(){
			$(this).removeClass('chatboxtextareaselected');
		});

		// Appui sur "Entrée" -> envoi de message
		$chatBox.off('keydown', '.chatboxtextarea');
		$chatBox.on('keydown', '.chatboxtextarea', function(e){
			if (e.ctrlKey && e.keyCode == 38) {
				$(this).val(lastMessageChat.replace(/<br \/>/g, "\n"));
				return false;
			}
			if (e.shiftKey === true)
				return true;
			if (e.keyCode !== 13)
				return true;
			var msgTxt = $(this).val();
			if (msgTxt == '') return false;
			var corresp = $(this).parents('.chatbox').attr('idCorresp');
			socket.emit('sendMsg', { text : msgTxt, user: { id: userID, pseudo: userLogin }, from: userID, to: corresp });
			$(this).val('');
			return false;
		});
	});

	// Quand on reçoit un msg depuis le serveur
	socket.on('newMsg', function(msg){
		if ((msg.to != userID && msg.from != userID) && msg.to != 999999)
			return;
		if (authChatRoom === false && msg.to == 999999)
			return;
		// Open window if needed
		var winID = (msg.to == 999999) ? "999999" : msg.from;
		if (openedChat.indexOf(winID) === -1)
			$('.avatarDiv[idUser="'+winID+'"]').click();
		// Append message
		$('.chatbox[idCorresp="'+msg.from+'"]').find('.chatboxtitle').removeClass('ui-state-disabled');
		appendMessage(msg);
		// Play sound
		if (chatSound === true && msg.from != userID && (msg.time - lastMessageTime) >= timeChatSoundOff && msg.to != 999999) {
			document.getElementById('soundchat').play();
		}
	});

	// Quand quelqu'un ouvre sa fenêtre de chat
	socket.on('chatOpened', function(users) {
//		console.log('User #'+users.from + ' OPENED a chat window.');
		$('.chatbox[idCorresp="'+users.from+'"]').find('.chatboxtitle').removeClass('ui-state-disabled');
	});

	// Quand quelqu'un ferme sa fenêtre de chat
	socket.on('chatClosed', function(users) {
//		console.log('User #'+users.from + ' CLOSED his chat window.');
		$('.chatbox[idCorresp="'+users.from+'"]').find('.chatboxtitle').addClass('ui-state-disabled');
	});

	// Récup de l'historique
	socket.on('gotHistory', function(history){
		for (var k in history)
			appendMessage(history[k]);
	});
};



function refreshAvatarList (usrList) {
	$('#avatarsList').html('');
	var effectiveUsrList = 0;
	$.each(usrList, function(i,usr){
		var commonProjs = usr.projects.filter(function(n) { return (userProjects.indexOf(n) != -1 && n != "1"); });
		var hidden = 'style="display:none;"';
		if (commonProjs.length >= 1 && usr.status <= (userStatus+1) && usr.id != userID) {
			hidden = '';
			effectiveUsrList += 1;
		}
		$('#avatarsList').append('<div class="avatarDiv" idUser="'+usr.id+'" pseudoUser="'+usr.pseudo+'" '+hidden+' title="'+usr.pseudo+' ('+usr.statusR+')"><img src="'+usr.avatar+'" /></div>');
	});
	authChatRoom = true;
	$('#avatarsList').prepend('<div class="avatarDiv" style="visibility:hidden !important;" idUser="999999" pseudoUser="everybody" title="Chat with everybody connected."><span class="ui-icon ui-icon-person"></span></div>'
				  +'<div class="inline mid colorSoft" style="visibility:hidden !important;"> | </div> ');
	if (effectiveUsrList >= 2)
		$('.avatarDiv[idUser=999999]').css('visibility', 'visible').next('.colorSoft').css('visibility', 'visible');
}

function appendMessage (msg) {
	if (typeof msg.user == 'undefined')
		return;
	if (authChatRoom === false && msg.to == 999999)
		return;
	// Format links
	var regLink = /https?:\/\//g;
	if (msg.text.match(regLink))
		msg.text = replaceLinksSO(msg.text);

	// Append message
	if (lastUsrCausant == msg.from && (msg.time - lastMessageTime) <= timeChatSoundOff*2) {
		$('.chatbox[idCorresp="'+msg.to+'"]').find('.msgTxt').last().append('<br />' + msg.text);
		$('.chatbox[idCorresp="'+msg.from+'"]').find('.msgTxt').last().append('<br />' + msg.text);
	}
	else {
		$('.chatbox[idCorresp="'+msg.to+'"]').find('.chatboxcontent').append(
			'<div class="chatboxmessage ui-corner-all">'
				+ '<div class="floatR ui-state-disabled petit">'+ msg.user.pseudo +' - '+msg.h+':'+msg.m+'</div>'
				+ '<div class="inline pad3 msgTxt">' + msg.text + '</div>'
			+'</div>');
		$('.chatbox[idCorresp="'+msg.from+'"]').find('.chatboxcontent').append(
			'<div class="chatboxmessage ui-corner-all">'
				+ '<div class="floatR ui-state-disabled petit">'+ msg.user.pseudo +' - '+msg.h+':'+msg.m+'</div>'
				+ '<div class="inline pad3 msgTxt">' + msg.text + '</div>'
			+'</div>');
	}
	if (msg.from == userID) {
		lastMessageTime = msg.time;
		lastMessageChat = msg.text;
	}
	var scroll_to   = $('.chatbox[idCorresp="'+msg.to+'"]').find('.chatboxcontent').prop('scrollHeight');
	var scroll_from = $('.chatbox[idCorresp="'+msg.from+'"]').find('.chatboxcontent').prop('scrollHeight');
	$('.chatbox[idCorresp="'+msg.to+'"]').find('.chatboxcontent').slimScroll({ scroll: scroll_to+'px' });
	$('.chatbox[idCorresp="'+msg.from+'"]').find('.chatboxcontent').slimScroll({ scroll: scroll_from+'px' });
	lastUsrCausant = msg.user.id;
}


function replaceLinksSO(text) {
    var rex = /(<a href=")?(?:https?:\/\/)?(?:(?:www)[-A-Za-z0-9+&@#\/%?=~_|$!:,.;]+\.)+[-A-Za-z0-9+&@#\/%?=~_|$!:,.;]+/ig;
    return text.replace(rex, function ( $0, $1 ) {
        if(/^https?:\/\/.+/i.test($0)) {
            return $1 ? $0: '<a href="'+$0+'" target="blank">'+$0+'</a>';
        }
        else {
            return $1 ? $0: '<a href="http://'+$0+'" target="blank">'+$0+'</a>';
        }
    });
}