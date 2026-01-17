import { SidebarCache } from './cache.js';

/**
 * Handles data fetching and caching for the sidebar
 */
export class SidebarDataService {
	#config;
	#cache;

	constructor(config) {
		this.#config = config;
		this.#cache = new SidebarCache(config.version);
	}

	/**
	 * Fetch sidebar data from cache or API
	 * @returns {Promise<Object|null>} Sidebar tree data or null on error
	 */
	async fetchData() {
		// Check cache first
		const cachedData = this.#cache.get();
		if (cachedData) {
			return cachedData;
		}

		// Fetch from API
		try {
			const response = await fetch(`${this.#config.apiUrl}?v=${this.#config.version}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' },
			});

			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			const tree = await response.json();
			this.#cache.set(tree);
			return tree;
		} catch (error) {
			console.error('Failed to load sidebar data:', error);
			return null;
		}
	}

	/**
	 * Clear the cached data
	 */
	clearCache() {
		this.#cache.clear();
	}
}
