import { SidebarContainer } from './sidebar/container.js';
import { ObsidianSidebar } from './sidebar/sidebar.js';

(() => {
	function initSidebar(config) {
		if (!config) {
			return;
		}

		const wrapper = document.body;
		const sidebarContainer = new SidebarContainer(wrapper);
		sidebarContainer.mount();

		const sidebar = new ObsidianSidebar(sidebarContainer, config);
		sidebar.load();
	}

	function enableAnimations() {
		document.body.classList.add('obsidian-animations');
	}

	document.addEventListener('obsidian/sidebar-loaded', (event) => {
		setTimeout(enableAnimations, 50);
	});

	initSidebar(window.obsidianSidebarConfig);
})();
