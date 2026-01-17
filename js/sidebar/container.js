import { SidebarHeader } from './header.js';

const CONTAINER_CLASS = 'obsidian-sidebar-container';
const STATE_WRAPPER_VISIBLE = 'has-obsidian-sidebar';
const STATE_INNER_HIDDEN = 'is-hidden';
const POSITION_LEFT = 'sidebar-position-left';
const POSITION_RIGHT = 'sidebar-position-right';
const MOBILE_BREAKPOINT = 1024;
const STORAGE_KEY = 'obsidian-sidebar-visible';

/**
 * Container for the sidebar DOM element
 */
export class SidebarContainer {
	#element;
	#contentWrapper;
	#wrapper;
	#header;
	#overlay;
	#isVisible = false;
	#modalEnabled = false;
	#position = 'left';

	constructor(wrapperElement, position = 'left', label = 'Navigation') {
		this.#wrapper = wrapperElement;

		this.#element = document.createElement('div');
		this.#element.className = CONTAINER_CLASS;

		// Create header
		this.#header = new SidebarHeader(label, position, this.toggle.bind(this));
		this.#element.appendChild(this.#header.getElement());

		// Create content wrapper for sidebar items
		this.#contentWrapper = document.createElement('div');
		this.#contentWrapper.className = 'sidebar-content';
		this.#element.appendChild(this.#contentWrapper);

		// Create overlay for modal behavior (click outside to close)
		this.#overlay = this.#createOverlay();

		this.setPosition(position);

		// Auto-hide sidebar and enable modal behavior on mobile
		// On desktop, restore saved state (default to visible)
		if (window.innerWidth > MOBILE_BREAKPOINT) {
			const savedState = this.loadState();
			if (savedState === false) {
				this.hide();
			} else {
				this.show();
			}
		} else {
			this.hide();
			this.enableModalBehavior();
		}
	}

	saveState(isVisible) {
		if (this.#modalEnabled) {
			return;
		}
		try {
			localStorage.setItem(STORAGE_KEY, JSON.stringify(isVisible));
		} catch (e) {
			// localStorage unavailable
		}
	}

	loadState() {
		try {
			const value = localStorage.getItem(STORAGE_KEY);
			return value !== null ? JSON.parse(value) : null;
		} catch (e) {
			return null;
		}
	}

	#createOverlay() {
		const overlay = document.createElement('div');
		overlay.className = 'sidebar-overlay';
		Object.assign(overlay.style, {
			position: 'fixed',
			inset: '0',
			zIndex: '99',
			background: 'rgba(0, 0, 0, 0.3)',
			opacity: '0',
			visibility: 'hidden',
			transition: 'opacity var(--animation-speed, 0.2s) var(--animation-type, ease)',
		});
		overlay.addEventListener('click', () => this.hide());
		return overlay;
	}

	showOverlay() {
		if (!this.#modalEnabled) {
			return;
		}
		this.#overlay.style.opacity = '1';
		this.#overlay.style.visibility = 'visible';
	}

	hideOverlay() {
		this.#overlay.style.opacity = '0';
		this.#overlay.style.visibility = 'hidden';
	}

	mount() {
		this.#wrapper.appendChild(this.#overlay);
		this.#wrapper.appendChild(this.#element);
		return this.#element;
	}

	unmount() {
		document.documentElement.classList.remove(STATE_WRAPPER_VISIBLE);
		this.#overlay.remove();
		this.#element.remove();
		this.#element = null;
		this.#isVisible = false;
	}

	show() {
		this.#isVisible = true;
		document.documentElement.classList.add(STATE_WRAPPER_VISIBLE);
		this.#element.classList.remove(STATE_INNER_HIDDEN);
		this.showOverlay();
		this.saveState(true);
	}

	hide() {
		this.#isVisible = false;
		document.documentElement.classList.remove(STATE_WRAPPER_VISIBLE);
		this.#element.classList.add(STATE_INNER_HIDDEN);
		this.hideOverlay();
		this.saveState(false);
	}

	enableModalBehavior() {
		this.#modalEnabled = true;
	}

	disableModalBehavior() {
		this.#modalEnabled = false;
		this.hideOverlay();
	}

	toggle() {
		if (this.#isVisible) {
			this.hide();
		} else {
			this.show();
		}
	}

	setPosition(side) {
		if (!['left', 'right'].includes(side)) {
			return;
		}

		this.#position = side;

		applyPositionClasses(this.#wrapper, this.#position);
		this.#header.setPosition(side);
	}

	isVisible() {
		return this.#isVisible;
	}

	getPosition() {
		return this.#position;
	}

	setLabel(label) {
		this.#header.setLabel(label);
	}

	getElement() {
		return this.#contentWrapper;
	}
}

const applyPositionClasses = (element, position) => {
	if (position === 'left') {
		element.classList.remove(POSITION_RIGHT);
		element.classList.add(POSITION_LEFT);
	}

	if (position === 'right') {
		element.classList.remove(POSITION_LEFT);
		element.classList.add(POSITION_RIGHT);
	}
};
