class Calc {
	constructor(id = 0) {
		this.id = id
		this.init()
	}

	init() {
		const calculator = document.querySelector(`#calc${this.id}`)
		const display = calculator.querySelector(`#calc${this.id} .calculator__display`)
		const keys = calculator.querySelector(`#calc${this.id} .calculator__keys`)

		keys.addEventListener('click', e => {
			if (!e.target.matches('button')) return

			const key = e.target
			const displayedNum = display.textContent
			const resultString = this.createResultString(key, displayedNum, calculator.dataset)

			display.textContent = resultString
			this.updateCalculatorState(key, calculator, resultString, displayedNum)
			this.updateVisualState(key, calculator)
		})
	}

	calculate(n1, operator, n2) {
		const firstNum = parseFloat(n1)
		const secondNum = parseFloat(n2)

		if (operator === 'add') return firstNum + secondNum
		if (operator === 'subtract') return firstNum - secondNum
		if (operator === 'multiply') return firstNum * secondNum
		if (operator === 'divide') return firstNum / secondNum
	}

	getKeyType(key) {
		const { action } = key.dataset

		if (!action) return 'number'
		if (
			action === 'add' ||
			action === 'subtract' ||
			action === 'multiply' ||
			action === 'divide'
		) return 'operator'

		return action
	}

	createResultString(key, displayedNum, state) {
		const keyContent = key.textContent
		const keyType = this.getKeyType(key)
		const {
			firstValue,
			operator,
			modValue,
			previousKeyType
		} = state

		if (keyType === 'number') {
			return displayedNum === '0' ||
				previousKeyType === 'operator' ||
				previousKeyType === 'calculate'
				? keyContent
				: displayedNum + keyContent
		}

		if (keyType === 'decimal') {
			if (!displayedNum.includes('.')) return displayedNum + '.'
			if (previousKeyType === 'operator' || previousKeyType === 'calculate') return '0.'
			return displayedNum
		}

		if (keyType === 'operator') {
			return firstValue &&
				operator &&
				previousKeyType !== 'operator' &&
				previousKeyType !== 'calculate'
				? this.calculate(firstValue, operator, displayedNum)
				: displayedNum
		}

		if (keyType === 'clear') return 0

		if (keyType === 'calculate') {
			return firstValue
				? previousKeyType === 'calculate'
					? this.calculate(displayedNum, operator, modValue)
					: this.calculate(firstValue, operator, displayedNum)
				: displayedNum
		}
	}

	updateCalculatorState(key, calculator, calculatedValue, displayedNum) {
		const keyType = this.getKeyType(key)
		const {
			firstValue,
			operator,
			modValue,
			previousKeyType
		} = calculator.dataset

		calculator.dataset.previousKeyType = keyType

		if (keyType === 'operator') {
			calculator.dataset.operator = key.dataset.action
			calculator.dataset.firstValue = firstValue &&
				operator &&
				previousKeyType !== 'operator' &&
				previousKeyType !== 'calculate'
				? calculatedValue
				: displayedNum
		}

		if (keyType === 'calculate') {
			calculator.dataset.modValue = firstValue && previousKeyType === 'calculate'
				? modValue
				: displayedNum
		}

		if (keyType === 'clear' && key.textContent === 'AC') {
			calculator.dataset.firstValue = ''
			calculator.dataset.modValue = ''
			calculator.dataset.operator = ''
			calculator.dataset.previousKeyType = ''
		}
	}

	updateVisualState(key, calculator) {
		const keyType = this.getKeyType(key)
		Array.from(key.parentNode.children).forEach(k => k.classList.remove('is-depressed'))

		if (keyType === 'operator') key.classList.add('is-depressed')
		if (keyType === 'clear' && key.textContent !== 'AC') key.textContent = 'AC'
		if (keyType !== 'clear') {
			const clearButton = calculator.querySelector('[data-action=clear]')
			clearButton.textContent = 'CE'
		}
	}
}
