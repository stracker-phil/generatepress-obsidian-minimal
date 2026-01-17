const STORAGE_KEY = 'obsidian_sidebar_cache';

/**
 * Cache for sidebar data with version checking
 * Pure data storage - no business logic
 */
export class SidebarCache {
	#version;

	constructor(version) {
		this.#version = version;
	}

	/**
	 * Get cached sidebar data if version matches
	 * @returns {Object|null} Cached data or null if invalid/missing
	 */
	get() {
		if (!window.localStorage) {
			return null;
		}

		try {
			const cached = localStorage.getItem(STORAGE_KEY);
			if (!cached) {
				return null;
			}

			const {
				version,
				data,
			} = JSON.parse(cached);

			if (version !== this.#version) {
				this.clear();
				return null;
			}

			return data;
		} catch (error) {
			console.error('Failed to read sidebar cache:', error);
			return null;
		}
	}

	/**
	 * Store sidebar data with version
	 */
	set(data) {
		if (!window.localStorage) {
			return;
		}

		try {
			const cache = {
				version: this.#version,
				data,
			};
			localStorage.setItem(STORAGE_KEY, JSON.stringify(cache));
		} catch (error) {
			console.error('Failed to save sidebar cache:', error);
		}
	}

	/**
	 * Clear cached data
	 */
	clear() {
		if (!window.localStorage) {
			return;
		}
		localStorage.removeItem(STORAGE_KEY);
	}
}
