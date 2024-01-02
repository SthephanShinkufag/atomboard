var settings = {};

/* ==[ Utils ]============================================================================================= */

function $q(path, rootEl) {
	return (rootEl || document.body).querySelector(path);
}

function $Q(path, rootEl) {
	return (rootEl || document.body).querySelectorAll(path);
}

function $id(id) {
	return document.getElementById(id);
}

function $ajax(method, url, loadFn) {
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = loadFn;
	xhr.open(method, url, true);
	xhr.send(null);
}

function getCookie(name) {
	var parts = ('; ' + document.cookie).split('; ' + name + '=');
	return parts.length === 2 ? parts.pop().split(';').shift() : null;
}

function saveSettings() {
	localStorage.atomSettings = JSON.stringify(settings);
}

/* ==[ Theme styles ]====================================================================================== */

function setThemeSelectors(value) {
	var els = $Q('.select-style');
	for (var i = 0, len = els.length; i < len; ++i) {
		els[i].value = value;
	}
}

// Used in html.php
function setThemeStyle(el) {
	var selectorValue = el.value;
	setThemeSelectors(selectorValue);
	document.documentElement.dataset.theme = settings.themeStyle = selectorValue;
	saveSettings();
}

/* ==[ Postform captcha ]================================================================================== */

function checkPasscode() {
	var captchaEl = $id('captchablock');
	if (!captchaEl || getCookie('passcode') !== '1') {
		return;
	}
	$ajax('GET', window.location.href.split('/res/')[0] + '/imgboard.php?passcode&check', function(e) {
		var xhr = e.target;
		if (xhr.readyState !== XMLHttpRequest.DONE) {
			return;
		}
		if (xhr.responseText === 'OK') {
			captchaEl.style.display = 'none';
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
	});
}

// Used in html.php
function reloadCaptcha() {
	var capEl = $id('captcha');
	capEl.value = '';
	capEl.focus();
	var capImage = $id('captchaimage');
	capImage.setAttribute('src', capImage.src + '#new');
	return false;
}

/* ==[ Postform passwords ]================================================================================ */

function initPasswords() {
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
}

/* ==[ Posts highlighting ]================================================================================ */

function initPostsHighlighting() {
	var highlightedUID = null;
	var handleEvent = function(e) {
		var targetEl = e.target;
		if (!targetEl.classList.contains('posteruid')) {
			return;
		}
		if (e.type === 'click') {
			// Unhighlight posts
			var els = $Q('.highlighted');
			for (var i = 0, len = els.length; i < len; ++i) {
				els[i].classList.remove('highlighted');
			}
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
	};
	document.body.addEventListener('click', handleEvent, true);
	document.body.addEventListener('mouseover', handleEvent, true);
}

/* ==[ Posts files expanding ]============================================================================= */

function hideFile(thumbEl, fileEl) {
	thumbEl.style.removeProperty('display');
	thumbEl.setAttribute('expanded', 'false');
	fileEl.style.display = 'none';
	fileEl.innerHTML = '';
}

// Used in html.php
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

/* ==[ Posts likes ]======================================================================================= */

// Used in html.php
function sendLike(likeEl, num) {
	$ajax('POST', window.location.href.split('/res/')[0] + '/imgboard.php?like=' + num, function(e) {
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
	});
}

/* ==[ Main ]============================================================================================== */

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
	document.addEventListener('DOMContentLoaded', function initAfterDOM() {
		checkPasscode(); // Check and apply passcode
		initPasswords(); // Set passwords for post form and deletion form
		setThemeSelectors(settings.themeStyle); // Set theme style selectors
		initPostsHighlighting(); // Events for highlighting posts by clicking on user ID
	});
}

main();
