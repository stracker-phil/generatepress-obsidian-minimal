import { SidebarDataService } from './data-service.js';
import { FolderStateManager } from './state.js';
import { folderTemplate } from './templates.js';

/**
 * Orchestrates sidebar rendering and interactions
 */
export class ObsidianSidebar {
	#container;
	#dataService;
	#stateManager;
	#eventListeners = [];

	constructor(container, config) {
		this.#container = container;
		this.#dataService = new SidebarDataService(config);
		this.#stateManager = new FolderStateManager();
	}

	async load() {
		const tree = await this.#dataService.fetchData();

		if (tree) {
			this.render(tree);
		}

		document.dispatchEvent(new CustomEvent('obsidian/sidebar-loaded', { detail: { tree } }));
	}

	render(tree) {
		const containerElement = this.#container.getElement();
		containerElement.innerHTML = Object.values(tree)
			.map(category => folderTemplate(category))
			.join('');
		containerElement.removeAttribute('data-loading');

		this.#initializeFolderToggles();
		this.#restoreFolderStates(containerElement);
	}

	destroy() {
		this.#eventListeners.forEach(({
			element,
			event,
			handler,
		}) => {
			element.removeEventListener(event, handler);
		});
		this.#eventListeners = [];
	}

	#addEventListener(element, event, handler) {
		element.addEventListener(event, handler);
		this.#eventListeners.push({
			element,
			event,
			handler,
		});
	}

	#initializeFolderToggles() {
		const containerElement = this.#container.getElement();
		const folderHeaders = containerElement.querySelectorAll('.tree-item-self.tree-type-folder');

		folderHeaders.forEach(header => {
			const clickHandler = () => {
				const treeItem = header.closest('.tree-item');
				const isExpanded = header.getAttribute('aria-expanded') === 'true';

				header.setAttribute('aria-expanded', !isExpanded);
				treeItem.classList.toggle('is-collapsed');

				this.#stateManager.saveState(treeItem.dataset.folder, !isExpanded);
			};

			const keyHandler = (e) => {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					clickHandler();
				}
			};

			this.#addEventListener(header, 'click', clickHandler);
			this.#addEventListener(header, 'keydown', keyHandler);
		});
	}

	#restoreFolderStates(containerElement) {
		const treeItems = containerElement.querySelectorAll('.tree-item[data-folder]');

		treeItems.forEach(treeItem => {
			const folderSlug = treeItem.dataset.folder;
			const isExpanded = this.#stateManager.getState(folderSlug);

			if (!isExpanded) {
				const header = treeItem.querySelector('.tree-item-self');
				header.setAttribute('aria-expanded', 'false');
				treeItem.classList.add('is-collapsed');
			}
		});
	}
}
