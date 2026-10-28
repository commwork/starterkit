(function () {
	var STORAGE_KEY = 'starterkit_heading_labels';
	var body = document.body;

	if (!body || !body.classList.contains('starterkit-heading-labels')) {
		return;
	}

	var toggle = document.getElementById('starterkit-heading-labels-toggle');

	if (!toggle) {
		var wrap = document.createElement('div');
		wrap.className = 'starterkit-heading-labels-toggle';
		toggle = document.createElement('button');
		toggle.type = 'button';
		toggle.className = 'starterkit-heading-labels-toggle__button';
		toggle.id = 'starterkit-heading-labels-toggle';
		toggle.setAttribute('aria-pressed', 'true');
		toggle.textContent = 'L +';
		wrap.appendChild(toggle);
		body.appendChild(wrap);
	}

	function setEnabled(enabled) {
		body.classList.toggle('starterkit-heading-labels--on', enabled);
		localStorage.setItem(STORAGE_KEY, enabled ? 'on' : 'off');
		toggle.setAttribute('aria-pressed', enabled ? 'true' : 'false');
		toggle.textContent = enabled
			? 'L -'
			: 'L +';
	}

	setEnabled(localStorage.getItem(STORAGE_KEY) !== 'off');

	toggle.addEventListener('click', function () {
		setEnabled(!body.classList.contains('starterkit-heading-labels--on'));
	});
})();
