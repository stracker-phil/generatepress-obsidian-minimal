import { getSidebarIcon } from './icons.js';

export class SidebarHeader {
	#element;
	#labelElement;
	#button;
	#position;
	#onToggle;

	constructor(label, position, onToggle) {
		this.#position = position;
		this.#onToggle = onToggle;

		// Create header container
		this.#element = document.createElement('div');
		this.#element.className = 'sidebar-header';

		// Create label
		this.#labelElement = document.createElement('span');
		this.#labelElement.className = 'sidebar-label';
		this.#labelElement.textContent = label;

		// Create toggle button
		this.#button = document.createElement('button');
		this.#button.className = 'sidebar-toggle';
		this.#button.type = 'button';
		this.#button.setAttribute('aria-label', 'Toggle sidebar');
		this.#button.innerHTML = getSidebarIcon(position);

		this.#button.addEventListener('click', () => {
			if (this.#onToggle) {
				this.#onToggle();
			}
		});

		this.#element.appendChild(this.#labelElement);
		this.#element.appendChild(this.#button);
	}

	setLabel(label) {
		this.#labelElement.textContent = label;
	}

	setPosition(position) {
		this.#position = position;
		this.#button.innerHTML = getSidebarIcon(position);
	}

	getElement() {
		return this.#element;
	}
}
