/**
 * Comportamentos do tema.
 *
 * Cada componente procura seu próprio HTML e falha em silêncio quando ele
 * não existe. Sem dependências e sem build.
 */
(function () {
	'use strict';

	/**
	 * Menu principal no mobile.
	 */
	function initMenuToggle() {
		var toggle = document.querySelector('[data-component="menu-toggle"]');

		if (!toggle) {
			return;
		}

		var nav = document.getElementById(toggle.getAttribute('aria-controls'));

		if (!nav) {
			return;
		}

		function setOpen(open) {
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			nav.classList.toggle('is-open', open);
		}

		toggle.addEventListener('click', function () {
			setOpen(toggle.getAttribute('aria-expanded') !== 'true');
		});

		document.addEventListener('keydown', function (event) {
			if ('Escape' === event.key && 'true' === toggle.getAttribute('aria-expanded')) {
				setOpen(false);
				toggle.focus();
			}
		});
	}

	initMenuToggle();
})();
