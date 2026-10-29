/**
 * Comportamentos do tema.
 *
 * Cada componente procura seu próprio HTML e falha em silêncio quando ele
 * não existe. Sem dependências e sem build.
 */
(function () {
	'use strict';

	var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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

	/**
	 * Fader comum para o hero e depoimentos.
	 *
	 * @param {Element} root Área do componente.
	 */
	function initFader(root) {
		var slides = Array.prototype.slice.call(root.querySelectorAll('[data-slide]'));
		var bullets = Array.prototype.slice.call(root.querySelectorAll('[data-slide-to]'));
		var toggle = root.querySelector('[data-rotation-toggle]');

		if (slides.length < 2 || !bullets.length || !toggle) {
			return;
		}

		var current = 0;
		var interval = parseInt(root.getAttribute('data-interval'), 10) || 6000;
		var timer = null;
		var playing = !reducedMotion;
		var hovered = false;
		var focusLocked = false;
		var hidden = document.hidden;

		function updateToggle() {
			var label = toggle.querySelector('[data-toggle-label]');
			var action = playing ? 'Pausar' : 'Retomar';

			toggle.setAttribute('aria-label', playing ? 'Pausar apresentação' : 'Retomar apresentação');
			toggle.setAttribute('aria-pressed', playing ? 'false' : 'true');
			if (label) {
				label.textContent = action;
			}
		}

		function stopTimer() {
			if (timer) {
				window.clearTimeout(timer);
				timer = null;
			}
		}

		function show(index) {
			current = (index + slides.length) % slides.length;

			slides.forEach(function (slide, slideIndex) {
				var active = slideIndex === current;

				slide.classList.toggle('is-active', active);
				slide.setAttribute('aria-hidden', active ? 'false' : 'true');
				if (active) {
					slide.removeAttribute('inert');
				} else {
					slide.setAttribute('inert', '');
				}
			});

			bullets.forEach(function (bullet, bulletIndex) {
				bullet.setAttribute('aria-current', bulletIndex === current ? 'true' : 'false');
			});
		}

		function schedule() {
			stopTimer();
			if (!playing || hovered || focusLocked || hidden) {
				return;
			}

			timer = window.setTimeout(function () {
				show(current + 1);
				schedule();
			}, interval);
		}

		function setPlaying(next) {
			playing = next;
			updateToggle();
			schedule();
		}

		bullets.forEach(function (bullet) {
			bullet.addEventListener('click', function () {
				show(parseInt(bullet.getAttribute('data-slide-to'), 10) || 0);
				schedule();
			});
		});

		var previous = root.querySelector('[data-slide-prev]');
		var next = root.querySelector('[data-slide-next]');
		if (previous) {
			previous.addEventListener('click', function () {
				show(current - 1);
				schedule();
			});
		}
		if (next) {
			next.addEventListener('click', function () {
				show(current + 1);
				schedule();
			});
		}

		toggle.addEventListener('click', function () {
			focusLocked = false;
			setPlaying(!playing);
		});

		root.addEventListener('pointerenter', function () {
			hovered = true;
			stopTimer();
		});

		root.addEventListener('pointerleave', function () {
			hovered = false;
			schedule();
		});

		root.addEventListener('focusin', function () {
			focusLocked = true;
			stopTimer();
		});

		root.addEventListener('focusout', function (event) {
			if (event.relatedTarget && root.contains(event.relatedTarget)) {
				return;
			}
			focusLocked = false;
			schedule();
		});

		document.addEventListener('visibilitychange', function () {
			hidden = document.hidden;
			schedule();
		});

		show(0);
		updateToggle();
		schedule();
	}

	/**
	 * Carrossel com scroll nativo, snap e arrasto por mouse.
	 *
	 * @param {Element} root Área do componente.
	 */
	function initCarousel(root) {
		var viewport = root.querySelector('[data-carousel-viewport]');
		var items = viewport ? Array.prototype.slice.call(viewport.querySelectorAll('.featured__item')) : [];

		if (!viewport || items.length < 2) {
			return;
		}

		var previous = root.querySelector('[data-carousel-prev]');
		var next = root.querySelector('[data-carousel-next]');
		var mouseDrag = null;
		var suppressClick = false;

		function behavior() {
			return reducedMotion ? 'auto' : 'smooth';
		}

		function centeredIndex() {
			var viewportLeft = viewport.getBoundingClientRect().left;
			var center = viewport.clientWidth / 2;
			var nearest = 0;
			var distance = Infinity;

			items.forEach(function (item, index) {
				var itemCenter = item.getBoundingClientRect().left - viewportLeft + item.offsetWidth / 2;
				var delta = Math.abs(center - itemCenter);

				if (delta < distance) {
					distance = delta;
					nearest = index;
				}
			});

			return nearest;
		}

		function goTo(index) {
			var item = items[Math.max(0, Math.min(items.length - 1, index))];
			if (!item) {
				return;
			}
			var itemLeft = item.getBoundingClientRect().left - viewport.getBoundingClientRect().left + viewport.scrollLeft;
			viewport.scrollTo({
				left: itemLeft - (viewport.clientWidth - item.offsetWidth) / 2,
				behavior: behavior()
			});
		}

		if (previous) {
			previous.addEventListener('click', function () {
				goTo(centeredIndex() - 1);
			});
		}
		if (next) {
			next.addEventListener('click', function () {
				goTo(centeredIndex() + 1);
			});
		}

		viewport.addEventListener('keydown', function (event) {
			if ('ArrowLeft' === event.key || 'ArrowRight' === event.key) {
				event.preventDefault();
				goTo(centeredIndex() + ('ArrowRight' === event.key ? 1 : -1));
			}
		});

		viewport.addEventListener('mousedown', function (event) {
			if (0 !== event.button) {
				return;
			}
			mouseDrag = { x: event.clientX, scrollLeft: viewport.scrollLeft, moved: false };
		});

		document.addEventListener('mousemove', function (event) {
			if (!mouseDrag) {
				return;
			}
			var distance = event.clientX - mouseDrag.x;
			if (!mouseDrag.moved && Math.abs(distance) > 5) {
				mouseDrag.moved = true;
				viewport.classList.add('is-dragging');
			}
			if (mouseDrag.moved) {
				event.preventDefault();
				viewport.scrollLeft = mouseDrag.scrollLeft - distance;
			}
		});

		document.addEventListener('mouseup', function () {
			if (!mouseDrag) {
				return;
			}
			if (mouseDrag.moved) {
				suppressClick = true;
				window.setTimeout(function () { suppressClick = false; }, 0);
			}
			mouseDrag = null;
			viewport.classList.remove('is-dragging');
		});

		viewport.addEventListener('click', function (event) {
			if (suppressClick) {
				event.preventDefault();
				event.stopPropagation();
			}
		}, true);
	}

	/**
	 * Lista de histórias, imagem de fundo e resumo sincronizados.
	 *
	 * @param {Element} root Área do componente.
	 */
	function initStories(root) {
		var list = root.querySelector('.stories__list');
		var backdrops = Array.prototype.slice.call(root.querySelectorAll('[data-story-backdrop]'));
		var heading = root.querySelector('[data-story-heading]');
		var summary = root.querySelector('[data-story-summary]');
		var announcement = root.querySelector('[data-story-announcement]');

		if (!list || !list.children.length || !heading || !summary) {
			return;
		}

		function entries() {
			return Array.prototype.slice.call(list.children);
		}

		function capturePositions() {
			return entries().map(function (entry) {
				return { entry: entry, top: entry.getBoundingClientRect().top };
			});
		}

		function animateOrder(previousPositions) {
			if (reducedMotion || !Element.prototype.animate) {
				return;
			}
			entries().forEach(function (entry) {
				var previous = previousPositions.filter(function (position) { return position.entry === entry; })[0];
				if (!previous) {
					return;
				}
				var distance = previous.top - entry.getBoundingClientRect().top;
				if (distance) {
					entry.animate(
						[
							{ transform: 'translateY(' + distance + 'px)' },
							{ transform: 'translateY(0)' }
						],
						{ duration: 420, easing: 'cubic-bezier(0.2, 0.7, 0.2, 1)' }
					);
				}
			});
		}

		function sync(announce, moveFocus) {
			var currentEntries = entries();
			var activeEntry = currentEntries[currentEntries.length - 1];
			var activeId = activeEntry.getAttribute('data-story-id');

			currentEntries.forEach(function (entry, index) {
				var active = index === currentEntries.length - 1;
				var link = entry.querySelector('[data-story-link]');
				var button = entry.querySelector('[data-story-select]');

				entry.classList.toggle('is-active', active);
				if (link) {
					link.hidden = !active;
					if (active) {
						link.setAttribute('aria-current', 'true');
					} else {
						link.removeAttribute('aria-current');
					}
				}
				if (button) {
					button.hidden = active;
					button.setAttribute('aria-pressed', 'false');
				}
			});

			backdrops.forEach(function (backdrop) {
				backdrop.classList.toggle('is-active', backdrop.getAttribute('data-story-backdrop') === activeId);
			});

			heading.textContent = activeEntry.getAttribute('data-story-title') || '';
			summary.textContent = activeEntry.getAttribute('data-story-summary') || '';
			if (announce && announcement) {
				announcement.textContent = 'História selecionada: ' + heading.textContent;
			}
			if (moveFocus) {
				var activeLink = activeEntry.querySelector('[data-story-link]');
				if (activeLink) {
					activeLink.focus();
				}
			}
		}

		function moveDown(announce, moveFocus) {
			var currentEntries = entries();
			if (currentEntries.length > 1) {
				var previousPositions = capturePositions();
				list.insertBefore(currentEntries[currentEntries.length - 1], currentEntries[0]);
				sync(announce, moveFocus);
				animateOrder(previousPositions);
			} else {
				sync(announce, moveFocus);
			}
		}

		function moveUp(announce, moveFocus) {
			var first = list.firstElementChild;
			if (first && list.children.length > 1) {
				var previousPositions = capturePositions();
				list.appendChild(first);
				sync(announce, moveFocus);
				animateOrder(previousPositions);
			} else {
				sync(announce, moveFocus);
			}
		}

		list.addEventListener('click', function (event) {
			var button = event.target.closest('[data-story-select]');
			if (!button) {
				return;
			}
			var previousPositions = capturePositions();
			list.appendChild(button.closest('[data-story-entry]'));
			sync(true, true);
			animateOrder(previousPositions);
		});

		var up = root.querySelector('[data-story-up]');
		var down = root.querySelector('[data-story-down]');
		if (up) {
			up.addEventListener('click', function () { moveUp(true, false); });
		}
		if (down) {
			down.addEventListener('click', function () { moveDown(true, false); });
		}

		root.addEventListener('keydown', function (event) {
			if ('ArrowUp' === event.key) {
				event.preventDefault();
				moveUp(true, true);
			} else if ('ArrowDown' === event.key) {
				event.preventDefault();
				moveDown(true, true);
			}
		});

		sync(false, false);
	}

	initMenuToggle();
	document.querySelectorAll('[data-component="hero"], [data-component="testimonials"]').forEach(initFader);
	document.querySelectorAll('[data-component="carousel"]').forEach(initCarousel);
	document.querySelectorAll('[data-component="stories"]').forEach(initStories);
})();
