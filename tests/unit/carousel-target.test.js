const test = require('node:test');
const assert = require('node:assert/strict');

const { chooseTarget } = require('../../tema-base/assets/js/carousel-target.js');

test('flick rápido que já passou do próximo card usa o card alcançado', () => {
	assert.equal(
		chooseTarget({
			startIndex: 0,
			velocity: 0.8,
			distance: 600,
			nearestIndex: 2,
			length: 4,
		}),
		2
	);
});

test('flick para a direita que já passou do anterior usa o card alcançado', () => {
	assert.equal(
		chooseTarget({
			startIndex: 2,
			velocity: -0.8,
			distance: -600,
			nearestIndex: 0,
			length: 4,
		}),
		0
	);
});

test('flick curto ainda avança exatamente um card', () => {
	assert.equal(
		chooseTarget({
			startIndex: 1,
			velocity: 0.8,
			distance: 60,
			nearestIndex: 1,
			length: 4,
		}),
		2
	);
});

test('arrasto lento curto volta para o card mais próximo', () => {
	assert.equal(
		chooseTarget({
			startIndex: 0,
			velocity: 0.1,
			distance: 80,
			nearestIndex: 0,
			length: 4,
		}),
		0
	);
});

test('flick rápido curto avança exatamente um card', () => {
	assert.equal(
		chooseTarget({
			startIndex: 0,
			velocity: 0.8,
			distance: 80,
			nearestIndex: 0,
			length: 4,
		}),
		1
	);
});

test('arrasto lento longo usa o card mais próximo', () => {
	assert.equal(
		chooseTarget({
			startIndex: 0,
			velocity: 0.1,
			distance: 600,
			nearestIndex: 1,
			length: 4,
		}),
		1
	);
});

test('flick para a direita volta exatamente um card', () => {
	assert.equal(
		chooseTarget({
			startIndex: 2,
			velocity: -0.8,
			distance: -80,
			nearestIndex: 2,
			length: 4,
		}),
		1
	);
});

test('flick no primeiro card não passa do limite', () => {
	assert.equal(
		chooseTarget({
			startIndex: 0,
			velocity: -0.8,
			distance: -80,
			nearestIndex: 0,
			length: 4,
		}),
		0
	);
});

test('flick no último card não passa do limite', () => {
	assert.equal(
		chooseTarget({
			startIndex: 3,
			velocity: 0.8,
			distance: 80,
			nearestIndex: 3,
			length: 4,
		}),
		3
	);
});

test('velocidade alta com deslocamento mínimo não navega', () => {
	assert.equal(
		chooseTarget({
			startIndex: 1,
			velocity: 0.8,
			distance: 5,
			nearestIndex: 1,
			length: 4,
		}),
		1
	);
});

test('nearestIndex fora do intervalo é limitado', () => {
	assert.equal(
		chooseTarget({
			startIndex: 0,
			velocity: 0,
			distance: 0,
			nearestIndex: 99,
			length: 4,
		}),
		3
	);
});

test('lista vazia devolve zero', () => {
	assert.equal(
		chooseTarget({
			startIndex: 0,
			velocity: 0.8,
			distance: 80,
			nearestIndex: 0,
			length: 0,
		}),
		0
	);
});
