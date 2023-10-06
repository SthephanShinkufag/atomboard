var highlightedUID = null;
var selectedText = '';
var settings = {};

function $q(path, rootEl) {
	return (rootEl || document.body).querySelector(path);
}

function $Q(path, rootEl) {
	return (rootEl || document.body).querySelectorAll(path);
}

function $id(id) {
	return document.getElementById(id);
}

function getCookie(name) {
	var parts = ('; ' + document.cookie).split('; ' + name + '=');
	return parts.length === 2 ? parts.pop().split(";").shift() : null;
}

function highlightPost(num) {
	unhighlightPosts();
	var post = $id('reply' + num) || $id('op' + num);
	if (post) {
		post.classList.add('highlighted');
	}
	return false;
}

function unhighlightPosts() {
	var els = $Q('.highlighted');
	for (var i = 0, len = els.length; i < len; ++i) {
		els[i].classList.remove('highlighted');
	}
}

function quotePost(num) {
	var el = $id('message');
	el.value += '>>' + num + '\n';
	el.focus();
	return false;
}

function reloadCaptcha() {
	var capEl = $id('captcha');
	capEl.value = '';
	capEl.focus();
	var capImage = $id('captchaimage');
	capImage.setAttribute('src', capImage.src + '#new');
	return false;
}

function hideFile(thumbEl, fileEl) {
	thumbEl.style.removeProperty('display');
	thumbEl.setAttribute('expanded', 'false');
	fileEl.style.display = 'none';
	fileEl.innerHTML = '';
}

function expandFile(e, id) {
	if (e !== undefined && e.which !== undefined && e.which !== 1) {
		return true;
	}
	var thumbEl = $id('thumbfile' + id);
	var fileEl = $id('file' + id);
	if (thumbEl.getAttribute('expanded') === 'true') {
		hideFile(thumbEl, fileEl);
		return false;
	}
	thumbEl.setAttribute('expanded', 'true');
	fileEl.innerHTML= decodeURIComponent($id('expand' + id).textContent);
	fileEl.style.visibility = 'hidden';
	setTimeout(function(id, thumbEl, fileEl) {
		return function() {
			thumbEl.style.display = 'none';
			fileEl.style.visibility = 'visible';
			fileEl.style.removeProperty('display');
			if (fileEl.firstElementChild.tagName !== 'VIDEO') {
				return;
			}
			fileEl.addEventListener('click', function(e) {
				if (e.clientY <= (e.target.getBoundingClientRect().bottom - 40)) {
					hideFile($id('thumbfile' + id), $id('file' + id));
				}
			}, true);
		}
	}(id, thumbEl, fileEl), 100);
	return false;
}

function textInsert(el, txt) {
	const scrtop = el.scrollTop;
	const start = el.selectionStart;
	el.value = el.value.substr(0, start) + txt + el.value.substr(el.selectionEnd);
	el.setSelectionRange(start + txt.length, start + txt.length);
	el.focus();
	el.scrollTop = scrtop;
}

function markupEvents(e) {
	if (e.type === 'mouseover') {
		selectedText = window.getSelection().toString();
		return;
	}
	var tag, msgEl = $id('message');
	var msgElVal = msgEl.value;
	var start = msgEl.selectionStart;
	var end = msgEl.selectionEnd;
	switch(e.target.id) {
	case 'markup-bold': tag = 'b'; break;
	case 'markup-italic': tag = 'i'; break;
	case 'markup-underline': tag = 'u'; break;
	case 'markup-strike': tag = 's'; break;
	case 'markup-spoiler': tag = 'spoiler'; break;
	case 'markup-code': tag = 'code'; break;
	case 'markup-quote':
		textInsert(msgEl, '> ' + (start === end ? selectedText : msgElVal.substring(start, end))
			.replace(/\n/gm, '\n> '));
		selectedText = '';
		e.preventDefault();
		return;
	}
	var scrtop = msgEl.scrtop;
	var str, len, val = msgElVal.substring(start, end);
	if (val.includes('\n')) {
		str = '[' + tag + ']' + val + '[/' + tag + ']';
		len = start + str.length;
	} else {
		var m = val.match(/^(\s*)(.*?)(\s*)$/);
		str = m[1] + '[' + tag + ']' + m[2] + '[/' + tag + ']' + m[3];
		len = start + (!m[2].length ? m[1].length + tag.length + 2 : str.length);
	}
	msgEl.value = msgElVal.substr(0, start) + str + msgElVal.substr(end);
	msgEl.setSelectionRange(len, len);
	msgEl.focus();
	msgEl.scrollTop = scrtop;
	e.preventDefault();
}

function sendLike(likeEl, num) {
	var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
	xhr.onreadystatechange = function(e) {
		var xhr = e.target;
		if (xhr.readyState !== 4 || xhr.status !== 200 && xhr.status !== 304) {
			return;
		}
		try {
			var obj = JSON.parse(xhr.responseText);
			if (obj.status === 'ok') {
				console.log(obj.message);
				likeEl.classList.toggle('like-enabled', obj.likes);
				likeEl.classList.toggle('like-disabled', !obj.likes);
				likeEl.nextElementSibling.textContent = obj.likes || '';
			}
		} catch(err) {
			console.log('Invalid response:\n' + err);
		}
	};
	xhr.open('POST', window.location.href.split('/res/')[0] + '/imgboard.php?like=' + num, true);
	xhr.send();
}

function setThemeStyle(el) {
	var selectorValue = el.value;
	var els = $Q('.select-style');
	for (var i = 0, len = els.length; i < len; ++i) {
		els[i].value = selectorValue;
	}
	document.documentElement.dataset.theme = settings.themeStyle = selectorValue;
	saveSettings();
}

function saveSettings() {
	localStorage.atomSettings = JSON.stringify(settings);
}

function initAfterDom() {
	// Navigate to post if hash available
	var hash = window.location.hash;
	if (hash) {
		var hashMatch = hash.match(/^#q([0-9]+)$/i);
		if (hashMatch && hashMatch[1]) {
			quotePost(hashMatch[1]);
		} else if ((hashMatch = hash.match(/^#([0-9]+)$/i)) && hashMatch[1]) {
			highlightPost(hashMatch[1]);
		}
	}

	// Check and enable posts moderation buttons
	if (getCookie('atom_access') === '1') {
		document.body.classList.add('access-enabled');
	}
	// Check and apply passcode
	if (getCookie('passcode') === '1') {
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function() {
			if (xhr.readyState === XMLHttpRequest.DONE) {
				if (xhr.responseText === "OK") {
					var captchaEl = $id('captchablock');
					if (captchaEl) {
						captchaEl.style.display = 'none';
					}
					var validCaptchaEl = $id('validcaptchablock');
					if (validCaptchaEl) {
						validCaptchaEl.style.display = '';
					}
				} else {
					var invalidCaptchaEl = $id('invalidcaptchablock');
					if (invalidCaptchaEl) {
						invalidCaptchaEl.style.display = '';
					}
				}
			}
		}
		xhr.open('GET', 'imgboard.php?passcode&check', true);
		xhr.send(null);
	}

	// Set passwords for post form and deletion form
	var replyPassw = $id('newpostpassword');
	var deletePassw = $id('deletepostpassword');
	if (replyPassw) {
		replyPassw.onchange = function(e) {
			var value = e.target.value;
			if (deletePassw) {
				deletePassw.value = value;
			}
			var expiration = new Date();
			expiration.setFullYear(expiration.getFullYear() + 7);
			document.cookie = 'atom_password=' + encodeURIComponent(value) +
				'; path=/; expires=' + expiration.toGMTString();
		};
	}
	var storedPassw = getCookie('atom_password');
	if (storedPassw) {
		if (replyPassw) {
			replyPassw.value = storedPassw;
		}
		if (deletePassw) {
			deletePassw.value = storedPassw;
		}
	}

	// Set theme style selectors
	var els = $Q('.select-style');
	for (var i = 0, len = els.length; i < len; ++i) {
		els[i].value = settings.themeStyle;
	}

	// Add events to markup buttons
	var markupBtns = $id('markup-buttons');
	if (markupBtns) {
		markupBtns.addEventListener('click', markupEvents);
		$id('markup-quote').addEventListener('mouseover', markupEvents);
	}

	// Highlight posts bu clicking on user ID
	var handleEvent = function(e) {
		var targetEl = e.target;
		if (targetEl.classList.contains('posteruid')) {
			if (e.type === 'click') {
				unhighlightPosts();
				var uid = targetEl.dataset.uid;
				if (highlightedUID === uid) {
					highlightedUID = null;
					e.preventDefault();
					return;
				}
				highlightedUID = uid;
				var matchedEls = $Q('.posteruid[data-uid="' + uid + '"]');
				for (var i = 0, len = matchedEls.length; i < len; ++i) {
					var post = matchedEls[i];
					while (!post.classList.contains('reply') && !post.classList.contains('oppost')) {
						post = post.parentNode;
					}
					post.classList.add('highlighted');
				}
				e.preventDefault();
			} else if (e.type === 'mouseover') {
				targetEl.title = 'Click to highlight posts by (' + targetEl.textContent + ')';
			}
		}
	};
	document.body.addEventListener('click', handleEvent, true);
	document.body.addEventListener('mouseover', handleEvent, true);
}

function main() {
	// Settings initialization
	try {
		settings = JSON.parse(localStorage.atomSettings);
	} catch(err) {
		saveSettings();
	}
	
	// Set theme style
	if (!settings.themeStyle) {
		settings.themeStyle = document.documentElement.dataset.theme;
		saveSettings();
	}
	document.documentElement.dataset.theme = settings.themeStyle;

	// Do stuff after DOM loading
	document.addEventListener('DOMContentLoaded', initAfterDom);

	// Check for Dollchan Extension and disable markup buttons
	window.addEventListener('load', function() {
		if (!!$id('de-main')) {
			$id('markup-buttons').style.display = 'none';
		}
	});
}

main();
