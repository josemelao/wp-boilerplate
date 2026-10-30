/**
 * Comportamentos do tema.
 *
 * Cada componente procura seu próprio HTML e falha em silêncio quando ele
 * não existe. Sem dependências e sem build.
 */
(function () {
	'use strict';

	var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var lastInputWasKeyboard = false;

	document.addEventListener('keydown', function () {
		lastInputWasKeyboard = true;
	});
	document.addEventListener('pointerdown', function () {
		lastInputWasKeyboard = false;
	}, true);

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
		var playing = true;
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
			if (!playing || focusLocked || hidden) {
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

		root.addEventListener('focusin', function () {
			if (!lastInputWasKeyboard) {
				return;
			}
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
	 * Carrossel com scroll nativo, snap e arrasto por ponteiro.
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
		var pointerDrag = null;
		var suppressClick = false;
		var suppressClickTimer = null;
		var settleTimer = null;
		var settleListener = null;

		function behavior() {
			return reducedMotion ? 'auto' : 'smooth';
		}

		function centeredIndex(scrollPosition) {
			var viewportLeft = viewport.getBoundingClientRect().left;
			var scrollLeft = 'number' === typeof scrollPosition ? scrollPosition : viewport.scrollLeft;
			var center = scrollLeft + viewport.clientWidth / 2;
			var nearest = 0;
			var distance = Infinity;

			items.forEach(function (item, index) {
				var itemCenter = item.getBoundingClientRect().left - viewportLeft + viewport.scrollLeft + item.offsetWidth / 2;
				var delta = Math.abs(center - itemCenter);

				if (delta < distance) {
					distance = delta;
					nearest = index;
				}
			});

			return nearest;
		}

		function scrollLeftFor(index) {
			var item = items[Math.max(0, Math.min(items.length - 1, index))];
			if (!item) {
				return viewport.scrollLeft;
			}
			var itemLeft = item.getBoundingClientRect().left - viewport.getBoundingClientRect().left + viewport.scrollLeft;
			return Math.max(0, Math.min(viewport.scrollWidth - viewport.clientWidth, itemLeft - (viewport.clientWidth - item.offsetWidth) / 2));
		}

		function goTo(index) {
			viewport.scrollTo({ left: scrollLeftFor(index), behavior: behavior() });
		}

		function finishSettling() {
			window.clearTimeout(settleTimer);
			if (settleListener) {
				viewport.removeEventListener('scrollend', settleListener);
				settleListener = null;
			}
			viewport.classList.remove('is-settling');
		}

		function settleAfterDrag(projectedScrollLeft) {
			var targetLeft = scrollLeftFor(centeredIndex(projectedScrollLeft));
			var needsAnimation = !reducedMotion && Math.abs(viewport.scrollLeft - targetLeft) > 1;

			if (!needsAnimation) {
				viewport.classList.remove('is-dragging', 'is-settling');
				return;
			}

			viewport.classList.add('is-settling');
			viewport.classList.remove('is-dragging');
			settleListener = finishSettling;
			viewport.addEventListener('scrollend', finishSettling);
			viewport.scrollTo({ left: targetLeft, behavior: 'smooth' });
			settleTimer = window.setTimeout(finishSettling, 1200);
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

		viewport.addEventListener('pointerdown', function (event) {
			if (!event.isPrimary || ('mouse' === event.pointerType && 0 !== event.button)) {
				return;
			}
			if (settleListener) {
				finishSettling();
				viewport.scrollTo({ left: viewport.scrollLeft, behavior: 'auto' });
			}
			pointerDrag = {
				id: event.pointerId,
				x: event.clientX,
				lastX: event.clientX,
				lastTime: event.timeStamp,
				velocity: 0,
				scrollLeft: viewport.scrollLeft,
				moved: false
			};
		});

		document.addEventListener('pointermove', function (event) {
			if (!pointerDrag || event.pointerId !== pointerDrag.id) {
				return;
			}
			var distance = event.clientX - pointerDrag.x;
			if (!pointerDrag.moved && Math.abs(distance) > 6) {
				pointerDrag.moved = true;
				viewport.classList.add('is-dragging');
			}
			if (pointerDrag.moved) {
				event.preventDefault();
				viewport.scrollLeft = pointerDrag.scrollLeft - distance;
				var elapsed = Math.max(1, event.timeStamp - pointerDrag.lastTime);
				var delta = event.clientX - pointerDrag.lastX;
				var instantVelocity = -delta / elapsed;
				pointerDrag.velocity = elapsed > 120 ? instantVelocity : (pointerDrag.velocity * 0.55) + (instantVelocity * 0.45);
			}
			pointerDrag.lastX = event.clientX;
			pointerDrag.lastTime = event.timeStamp;
		}, { passive: false });

		function finishPointerDrag(event) {
			if (!pointerDrag || (event && event.pointerId !== pointerDrag.id)) {
				return;
			}
			var drag = pointerDrag;
			var didMove = drag.moved;
			var projectedScrollLeft = viewport.scrollLeft;
			if (didMove && event && 'pointerup' === event.type) {
				var releaseAge = Math.max(0, event.timeStamp - drag.lastTime);
				var momentum = drag.velocity * 210 * Math.exp(-releaseAge / 100);
				var slideStep = Math.abs(items[1].getBoundingClientRect().left - items[0].getBoundingClientRect().left);
				var momentumLimit = Math.min(320, slideStep * 0.35);
				momentum = Math.max(-momentumLimit, Math.min(momentumLimit, momentum));
				projectedScrollLeft = Math.max(0, Math.min(viewport.scrollWidth - viewport.clientWidth, viewport.scrollLeft + momentum));
			}
			if (didMove) {
				suppressClick = true;
				window.clearTimeout(suppressClickTimer);
				suppressClickTimer = window.setTimeout(function () {
					suppressClick = false;
				}, 400);
			}
			pointerDrag = null;
			if (didMove) {
				settleAfterDrag(projectedScrollLeft);
			} else {
				viewport.classList.remove('is-dragging');
			}
		}

		document.addEventListener('pointerup', finishPointerDrag);
		document.addEventListener('pointercancel', finishPointerDrag);

		viewport.addEventListener('dragstart', function (event) {
			if (pointerDrag) {
				event.preventDefault();
			}
		});

		viewport.addEventListener('click', function (event) {
			if (suppressClick) {
				event.preventDefault();
				event.stopImmediatePropagation();
				suppressClick = false;
				window.clearTimeout(suppressClickTimer);
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
		var announcement = root.querySelector('[data-story-announcement]');
		var animating = false;
		var duration = 700;

		if (!list || !list.children.length) {
			return;
		}

		function entries() {
			return Array.prototype.filter.call(list.children, function (entry) {
				return entry.hasAttribute('data-story-entry');
			});
		}

		function setEntryState(entry, active, visible) {
			var link = entry.querySelector('[data-story-link]');
			var button = entry.querySelector('[data-story-select]');
			var teaser = entry.querySelector('[data-story-summary]');

			entry.classList.toggle('is-active', active);
			entry.classList.toggle('is-visible', visible);
			entry.setAttribute('aria-hidden', visible ? 'false' : 'true');
			if (visible) {
				entry.removeAttribute('inert');
			} else {
				entry.setAttribute('inert', '');
			}
			if (link) {
				link.hidden = !active;
				if (active) {
					link.setAttribute('aria-current', 'page');
				} else {
					link.removeAttribute('aria-current');
				}
			}
			if (button) {
				button.hidden = active;
				button.setAttribute('aria-pressed', active ? 'true' : 'false');
			}
			if (teaser) {
				teaser.hidden = !active;
			}
		}

		function setEntryGeometry(entry, top, active) {
			entry.style.setProperty('--story-top', top + '%');
			entry.style.setProperty('--story-height', (active ? 34 : 22) + '%');
			entry.style.setProperty('--story-left', (active ? 50 : 53.2) + '%');
			entry.style.setProperty('--story-width', (active ? 50 : 26) + '%');
		}

		function sync(announce, moveFocus) {
			var currentEntries = entries();
			var activeEntry = currentEntries[currentEntries.length - 1];
			var activeId = activeEntry.getAttribute('data-story-id');
			var visibleStart = Math.max(0, currentEntries.length - 4);
			var visibleCount = currentEntries.length - visibleStart;
			var visibleHeight = ((visibleCount - 1) * 22) + 34;
			var topOffset = (100 - visibleHeight) / 2;
			list.parentElement.style.setProperty('--story-active-top', (topOffset + ((visibleCount - 1) * 22)) + '%');

			currentEntries.forEach(function (entry, index) {
				var active = index === currentEntries.length - 1;
				var visible = index >= visibleStart;
				var slot = index - visibleStart;

				setEntryState(entry, active, visible);
				setEntryGeometry(entry, topOffset + (slot * 22), active);
			});

			backdrops.forEach(function (backdrop) {
				backdrop.classList.toggle('is-active', backdrop.getAttribute('data-story-backdrop') === activeId);
			});

			if (announce && announcement) {
				announcement.textContent = 'História selecionada: ' + (activeEntry.getAttribute('data-story-title') || '');
			}
			if (moveFocus) {
				var activeListLink = activeEntry.querySelector('[data-story-link]');
				if (activeListLink) {
					activeListLink.focus();
				}
			}
		}

		function rotate(direction, announce, moveFocus, targetEntry) {
			var currentEntries = entries();
			var count = currentEntries.length;
			var oldStart = Math.max(0, count - 4);
			var nextEntries = 'down' === direction
				? [currentEntries[count - 1]].concat(currentEntries.slice(0, count - 1))
				: currentEntries.slice(1).concat(currentEntries[0]);
			var nextStart = Math.max(0, count - 4);
			var nextVisibleCount = count - nextStart;
			var nextOffset = (100 - (((nextVisibleCount - 1) * 22) + 34)) / 2;
			var outgoing = 'down' === direction ? currentEntries[count - 1] : currentEntries[oldStart];
			var wrapEntry = count <= 4 ? outgoing : null;
			var ghost = null;
			var moving = [];

			if (animating || count < 2) {
				return;
			}
			animating = true;

			if (wrapEntry) {
				ghost = wrapEntry.cloneNode(true);
				ghost.removeAttribute('data-story-entry');
				ghost.setAttribute('data-story-ghost', '');
				ghost.setAttribute('aria-hidden', 'true');
				ghost.setAttribute('inert', '');
				ghost.style.pointerEvents = 'none';
				setEntryState(ghost, 'up' === direction, true);
				ghost.setAttribute('aria-hidden', 'true');
				ghost.setAttribute('inert', '');
				list.appendChild(ghost);
			}

			currentEntries.forEach(function (entry, index) {
				var nextIndex = nextEntries.indexOf(entry);
				var visibleBefore = index >= oldStart;
				var visibleAfter = nextIndex >= nextStart;
				var active = nextIndex === count - 1;
				var visible = visibleBefore || visibleAfter || entry === outgoing;
				var incomingFromBottom = 'up' === direction && entry === currentEntries[0];

				if (incomingFromBottom) {
					entry.style.transition = 'none';
				}
				setEntryState(entry, active, visible);
				if (visible) {
					moving.push(entry);
				} else if (!incomingFromBottom) {
					entry.style.transition = 'none';
				}

				if (incomingFromBottom) {
					setEntryGeometry(entry, 100, active);
					entry.offsetHeight;
					entry.style.transition = '';
				}
			});

			if (ghost) {
				var ghostTop = 'down' === direction ? nextOffset - 22 : 100;
				ghost.style.transition = 'none';
				setEntryGeometry(ghost, ghostTop, 'up' === direction);
				ghost.offsetHeight;
				ghost.style.transition = '';
				moving.push(ghost);
			}

			list.parentElement.style.setProperty('--story-active-top', (nextOffset + ((nextVisibleCount - 1) * 22)) + '%');
			list.offsetHeight;

			currentEntries.forEach(function (entry, index) {
				var nextIndex = nextEntries.indexOf(entry);
				var active = nextIndex === count - 1;
				var targetTop = nextOffset + ((nextIndex - nextStart) * 22);

				if ('down' === direction && entry === outgoing) {
					targetTop = 100;
				} else if ('up' === direction && entry === currentEntries[oldStart]) {
					targetTop = nextOffset - 22;
				}
				if (moving.indexOf(entry) !== -1) {
					setEntryGeometry(entry, targetTop, active);
				} else {
					setEntryGeometry(entry, targetTop, active);
					entry.style.transition = '';
				}
			});

			if (ghost) {
				var ghostTargetTop = 'down' === direction ? nextOffset : nextOffset + ((nextVisibleCount - 1) * 22);
				setEntryGeometry(ghost, ghostTargetTop, 'up' === direction);
			}

			window.setTimeout(function () {
				if (ghost) {
					ghost.remove();
				}
				nextEntries.forEach(function (entry) {
					list.appendChild(entry);
				});
				animating = false;
				sync(announce, moveFocus && (!targetEntry || nextEntries[nextEntries.length - 1] === targetEntry));

				if (targetEntry && entries()[entries().length - 1] !== targetEntry) {
					var targetIndex = entries().indexOf(targetEntry);
					var downSteps = entries().length - 1 - targetIndex;
					var upSteps = targetIndex + 1;
					rotate(downSteps <= upSteps ? 'down' : 'up', announce, moveFocus, targetEntry);
				}
			}, duration);
		}

		list.addEventListener('click', function (event) {
			var button = event.target.closest('[data-story-select]');
			if (button) {
				event.preventDefault();
				var targetEntry = button.closest('[data-story-entry]');
				var targetIndex = entries().indexOf(targetEntry);
				var downSteps = entries().length - 1 - targetIndex;
				var upSteps = targetIndex + 1;
				rotate(downSteps <= upSteps ? 'down' : 'up', true, true, targetEntry);
			}
		});

		root.addEventListener('click', function (event) {
			if (event.target.closest('[data-story-up]')) {
				rotate('up', true, false);
			} else if (event.target.closest('[data-story-down]')) {
				rotate('down', true, false);
			}
		});

		root.addEventListener('keydown', function (event) {
			if ('ArrowUp' === event.key) {
				event.preventDefault();
				rotate('up', true, true);
			} else if ('ArrowDown' === event.key) {
				event.preventDefault();
				rotate('down', true, true);
			}
		});

		sync(false, false);
	}

	initMenuToggle();
	document.querySelectorAll('[data-component="hero"], [data-component="testimonials"]').forEach(initFader);
	document.querySelectorAll('[data-component="carousel"]').forEach(initCarousel);
	document.querySelectorAll('[data-component="stories"]').forEach(initStories);
})();
