/**
 * Decisão pura do card de destino do carrossel de destaques.
 *
 * Velocidade e distância positivas significam avançar (próximo card).
 * Um flick curto avança um card; se o gesto já passou de um card,
 * o destino acompanha o card mais próximo alcançado.
 * Um arrasto lento usa o card mais próximo da posição atual.
 *
 * O arquivo funciona como módulo CommonJS nos testes e como global
 * `window.tbCarouselTarget` no navegador.
 */
(function (root, factory) {
	'use strict';

	var api = factory();

	if (typeof module === 'object' && module.exports) {
		module.exports = api;
	}

	if (root) {
		root.tbCarouselTarget = api;
	}
})(typeof globalThis !== 'undefined' ? globalThis : this, function () {
	'use strict';

	/**
	 * Limita um índice ao intervalo válido.
	 *
	 * @param {number} index  Índice desejado.
	 * @param {number} length Quantidade de itens.
	 * @return {number}
	 */
	function clampIndex(index, length) {
		if (!length || length < 1) {
			return 0;
		}

		return Math.max(0, Math.min(length - 1, index));
	}

	/**
	 * Escolhe o índice de destino depois de um arrasto.
	 *
	 * @param {Object} options             Opções.
	 * @param {number} options.startIndex  Índice central no início do gesto.
	 * @param {number} options.velocity    Velocidade em px/ms (positiva = próximo).
	 * @param {number} options.distance    Deslocamento no sentido do scroll (positivo = próximo).
	 * @param {number} options.nearestIndex Índice mais próximo ao soltar.
	 * @param {number} options.length      Quantidade de cards.
	 * @param {number} [options.flickVelocity] Velocidade mínima do flick.
	 * @param {number} [options.minDrag]   Deslocamento mínimo para considerar flick.
	 * @return {number}
	 */
	function chooseTarget(options) {
		var opts = options || {};
		var length = Math.max(0, Number(opts.length) || 0);
		var startIndex = clampIndex(Number(opts.startIndex) || 0, length);
		var nearestIndex = clampIndex(Number(opts.nearestIndex) || 0, length);
		var velocity = Number(opts.velocity) || 0;
		var distance = Number(opts.distance) || 0;
		var flickVelocity = 'number' === typeof opts.flickVelocity ? opts.flickVelocity : 0.32;
		var minDrag = 'number' === typeof opts.minDrag ? opts.minDrag : 16;
		var isFlick = Math.abs(velocity) >= flickVelocity && Math.abs(distance) >= minDrag;

		if (!isFlick) {
			return nearestIndex;
		}

		var direction = distance >= 0 ? 1 : -1;
		var target = startIndex + direction;

		// Nunca volte para trás depois que o gesto já passou de um card.
		target = direction > 0 ? Math.max(target, nearestIndex) : Math.min(target, nearestIndex);

		return clampIndex(target, length);
	}

	return {
		chooseTarget: chooseTarget,
		clampIndex: clampIndex,
	};
});
