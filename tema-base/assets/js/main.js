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
		var focusLocked = false;
		var hoverPaused = false;
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
				var title = slide.querySelector('.hero__title');

				if (title) {
					var headingTag = active ? 'H1' : 'H2';
					if (title.tagName !== headingTag) {
						var replacement = document.createElement(headingTag.toLowerCase());
						replacement.className = title.className;
						replacement.textContent = title.textContent;
						title.replaceWith(replacement);
					}
				}

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
			if (!playing || focusLocked || hoverPaused || hidden) {
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

		root.addEventListener('mouseenter', function () {
			hoverPaused = true;
			stopTimer();
		});

		root.addEventListener('mouseleave', function () {
			hoverPaused = false;
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
		var targetApi = window.tbCarouselTarget || {};
		var FLICK_VELOCITY = 0.32;
		var MIN_DRAG = 16;
		var RELEASE_DECAY = 90;
		var SPRING_OMEGA = 15;
		var DRAG_RESISTANCE = 0.9;

		if (!viewport || items.length < 2) {
			return;
		}

		var previous = root.querySelector('[data-carousel-prev]');
		var next = root.querySelector('[data-carousel-next]');
		var pointerDrag = null;
		var suppressClick = false;
		var suppressClickTimer = null;
		var dragFrame = null;
		var scrollFrame = null;

		/**
		 * Limita um valor ao intervalo.
		 *
		 * @param {number} value Valor.
		 * @param {number} min   Mínimo.
		 * @param {number} max   Máximo.
		 * @return {number}
		 */
		function clamp(value, min, max) {
			return Math.max(min, Math.min(max, value));
		}

		/**
		 * Índice do card mais próximo do centro do viewport.
		 *
		 * @return {number}
		 */
		function centeredIndex() {
			var viewportLeft = viewport.getBoundingClientRect().left;
			var center = viewport.scrollLeft + viewport.clientWidth / 2;
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

		/**
		 * Posição de scroll que centraliza um card.
		 *
		 * @param {number} index Índice.
		 * @return {number}
		 */
		function scrollLeftFor(index) {
			var item = items[clamp(index, 0, items.length - 1)];
			if (!item) {
				return viewport.scrollLeft;
			}
			var itemLeft = item.getBoundingClientRect().left - viewport.getBoundingClientRect().left + viewport.scrollLeft;
			return Math.max(0, Math.min(viewport.scrollWidth - viewport.clientWidth, itemLeft - (viewport.clientWidth - item.offsetWidth) / 2));
		}

		/**
		 * Expõe o índice central para testes e leitores de tela.
		 *
		 * @param {number} index Índice.
		 * @return {void}
		 */
		function markCentered(index) {
			viewport.setAttribute('data-centered-index', String(clamp(index, 0, items.length - 1)));
		}

		/**
		 * Cancela a animação de scroll em andamento.
		 *
		 * @return {void}
		 */
		function cancelScrollAnimation() {
			if (scrollFrame) {
				window.cancelAnimationFrame(scrollFrame);
				scrollFrame = null;
			}
			viewport.classList.remove('is-settling');
		}

		/**
		 * Desliza até o card alvo com uma mola criticamente amortecida.
		 *
		 * A posição inicial e a velocidade vêm do gesto atual, para o primeiro
		 * frame continuar o movimento em vez de dar um salto seco.
		 *
		 * @param {number} index           Índice de destino.
		 * @param {number} initialVelocity Velocidade inicial em px/ms (positiva = próximo).
		 * @return {void}
		 */
		function animateScrollTo(index, initialVelocity) {
			var targetLeft = scrollLeftFor(index);
			var startLeft = viewport.scrollLeft;
			var offset = startLeft - targetLeft;

			cancelScrollAnimation();
			markCentered(index);

			if (reducedMotion || Math.abs(offset) < 1) {
				viewport.scrollLeft = targetLeft;
				return;
			}

			var velocity = (initialVelocity || 0) * 1000;
			var limit = SPRING_OMEGA * Math.abs(offset);
			var startTime = null;

			velocity = Math.max(-limit, Math.min(limit, velocity));

			viewport.classList.add('is-settling');

			function frame(now) {
				if (null === startTime) {
					startTime = now;
				}

				var elapsed = (now - startTime) / 1000;
				var damping = Math.exp(-SPRING_OMEGA * elapsed);
				var displacement = (offset + ((velocity + (SPRING_OMEGA * offset)) * elapsed)) * damping;
				var currentVelocity = (velocity - (SPRING_OMEGA * (velocity + (SPRING_OMEGA * offset)) * elapsed)) * damping;

				viewport.scrollLeft = targetLeft + displacement;

				if (Math.abs(displacement) < 0.5 && Math.abs(currentVelocity) < 30) {
					viewport.scrollLeft = targetLeft;
					scrollFrame = null;
					viewport.classList.remove('is-settling');
					return;
				}

				scrollFrame = window.requestAnimationFrame(frame);
			}

			scrollFrame = window.requestAnimationFrame(frame);
		}

		/**
		 * Navega para um card.
		 *
		 * @param {number} index Índice.
		 * @return {void}
		 */
		function goTo(index) {
			animateScrollTo(index, 0);
		}

		/**
		 * Decide o destino depois do arrasto.
		 *
		 * @param {Object} options Opções.
		 * @return {number}
		 */
		function chooseTarget(options) {
			if (targetApi && 'function' === typeof targetApi.chooseTarget) {
				return targetApi.chooseTarget(options);
			}

			// Fallback mínimo se o script auxiliar não tiver carregado.
			var isFlick = Math.abs(options.velocity) >= FLICK_VELOCITY && Math.abs(options.distance) >= MIN_DRAG;
			var target = isFlick ? options.startIndex + (options.velocity > 0 ? 1 : -1) : options.nearestIndex;

			return clamp(target, 0, options.length - 1);
		}

		/**
		 * Aplica o último movimento do ponteiro no próximo frame.
		 *
		 * @return {void}
		 */
		function applyDragFrame() {
			dragFrame = null;

			if (!pointerDrag || null === pointerDrag.pendingX) {
				return;
			}

			viewport.scrollLeft = pointerDrag.scrollLeft - ((pointerDrag.pendingX - pointerDrag.x) * DRAG_RESISTANCE);
			pointerDrag.pendingX = null;
		}

		/**
		 * Finaliza o arrasto e inicia o settle.
		 *
		 * @param {PointerEvent} event Evento.
		 * @return {void}
		 */
		function finishPointerDrag(event) {
			if (!pointerDrag || (event && event.pointerId !== pointerDrag.id)) {
				return;
			}

			var drag = pointerDrag;
			var didMove = drag.moved;
			var finalX = event && 'pointercancel' !== event.type && 'number' === typeof event.clientX ? event.clientX : drag.lastX;

			pointerDrag = null;

			if (dragFrame) {
				window.cancelAnimationFrame(dragFrame);
				dragFrame = null;
			}

			if (!didMove) {
				viewport.classList.remove('is-dragging');
				return;
			}

			var releaseAge = Math.max(
				0,
				(event && 'number' === typeof event.timeStamp ? event.timeStamp : drag.lastTime) - drag.lastTime
			);
			var releaseVelocity = drag.velocity * Math.exp(-releaseAge / RELEASE_DECAY);
			var isCancel = event && 'pointercancel' === event.type;
			var targetIndex = chooseTarget({
				startIndex: drag.startIndex,
				velocity: isCancel ? 0 : releaseVelocity,
				distance: drag.x - finalX,
				nearestIndex: centeredIndex(),
				length: items.length,
				flickVelocity: FLICK_VELOCITY,
				minDrag: MIN_DRAG
			});

			suppressClick = true;
			window.clearTimeout(suppressClickTimer);
			suppressClickTimer = window.setTimeout(function () {
				suppressClick = false;
			}, 400);

			animateScrollTo(targetIndex, isCancel ? 0 : releaseVelocity);
			viewport.classList.remove('is-dragging');
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

			cancelScrollAnimation();

			pointerDrag = {
				id: event.pointerId,
				x: event.clientX,
				lastX: event.clientX,
				lastTime: event.timeStamp,
				velocity: 0,
				scrollLeft: viewport.scrollLeft,
				moved: false,
				startIndex: centeredIndex(),
				pendingX: null
			};
		});

		document.addEventListener('pointermove', function (event) {
			if (!pointerDrag || event.pointerId !== pointerDrag.id) {
				return;
			}

			var distance = event.clientX - pointerDrag.x;

			if (!pointerDrag.moved && Math.abs(distance) > 6) {
				pointerDrag.moved = true;
				try {
					viewport.setPointerCapture(event.pointerId);
				} catch (error) {
					// O ponteiro pode já ter sido liberado; o arrasto continua via document.
				}
				viewport.classList.add('is-dragging');
			}

			if (pointerDrag.moved) {
				event.preventDefault();
				pointerDrag.pendingX = event.clientX;

				if (!dragFrame) {
					dragFrame = window.requestAnimationFrame(applyDragFrame);
				}

				var elapsed = Math.max(1, event.timeStamp - pointerDrag.lastTime);
				var delta = event.clientX - pointerDrag.lastX;
				var instantVelocity = -delta / elapsed;

				pointerDrag.velocity = elapsed > 120 ? instantVelocity : (pointerDrag.velocity * 0.55) + (instantVelocity * 0.45);
			}

			pointerDrag.lastX = event.clientX;
			pointerDrag.lastTime = event.timeStamp;
		}, { passive: false });

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

		window.addEventListener('resize', function () {
			viewport.scrollLeft = scrollLeftFor(centeredIndex());
		});

		markCentered(centeredIndex());
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
			var label = entry.querySelector('[data-story-label]');
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
					link.setAttribute('aria-current', 'true');
				} else {
					link.removeAttribute('aria-current');
				}
			}
			if (label) {
				label.hidden = active;
			}
			if (teaser) {
				teaser.setAttribute('aria-hidden', active ? 'false' : 'true');
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

		function rotate(direction, announce, moveFocus) {
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
					// Posiciona fora da seção sem animar a geometria, mas restaura
					// a transição antes de ativar a cor para o fade acontecer.
					entry.style.transition = 'none';
					setEntryGeometry(entry, 100, active);
					entry.offsetHeight;
					entry.style.transition = '';
				}

				setEntryState(entry, active, visible);

				if (visible) {
					moving.push(entry);
				} else if (!incomingFromBottom) {
					entry.style.transition = 'none';
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
				sync(announce, moveFocus);
			}, duration);
		}

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

	/**
	 * Copia o link de um artigo/história.
	 */
	function initShare() {
		var buttons = document.querySelectorAll('[data-share-copy]');

		if (!buttons.length) {
			return;
		}

		Array.prototype.forEach.call(buttons, function (button) {
			button.addEventListener('click', function () {
				var url = button.getAttribute('data-url') || window.location.href;
				var share = button.closest('.share');
				var status = share ? share.querySelector('[data-share-status]') : null;

				function announce(message) {
					if (status) {
						status.textContent = message;
					}
				}

				function fallback() {
					var field = document.createElement('textarea');
					var copied = false;

					field.value = url;
					field.setAttribute('readonly', '');
					field.style.position = 'fixed';
					field.style.opacity = '0';
					document.body.appendChild(field);
					field.select();

					try {
						copied = document.execCommand('copy');
					} catch (error) {
						copied = false;
					}

					document.body.removeChild(field);
					announce(copied ? 'Link copiado.' : 'Não foi possível copiar. Use o endereço do navegador.');
				}

				if (navigator.clipboard && window.isSecureContext) {
					navigator.clipboard.writeText(url).then(function () {
						announce('Link copiado.');
					}).catch(fallback);
				} else {
					fallback();
				}
			});
		});
	}

	initMenuToggle();
	document.querySelectorAll('[data-component="hero"], [data-component="testimonials"]').forEach(initFader);
	document.querySelectorAll('[data-component="carousel"]').forEach(initCarousel);
	document.querySelectorAll('[data-component="stories"]').forEach(initStories);
	initShare();
})();
