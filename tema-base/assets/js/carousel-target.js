/**
 * Decisão pura do card de destino do carrossel de destaques.
 *
 * Velocidade positiva significa arrasto para a esquerda (próximo card).
 * Um flick rápido vai exatamente um card; um arrasto lento usa o card
 * mais próximo da posição atual.
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
	 * @param {number} options.distance    Deslocamento do ponteiro em px.
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
		var flickVelocity = 'number' === typeof opts.flickVelocity ? opts.flickVelocity : 0.2;
		var minDrag = 'number' === typeof opts.minDrag ? opts.minDrag : 10;
		var isFlick = Math.abs(velocity) >= flickVelocity && Math.abs(distance) >= minDrag;

		if (!isFlick) {
			return nearestIndex;
		}

		return clampIndex(startIndex + (velocity > 0 ? 1 : -1), length);
	}

	return {
		chooseTarget: chooseTarget,
		clampIndex: clampIndex,
	};
});
