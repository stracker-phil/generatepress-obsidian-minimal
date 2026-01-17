const STORAGE_KEY = 'obsidian_folder_states';

/**
 * Manages folder expand/collapse states
 * Pure state management - no DOM manipulation
 */
export class FolderStateManager {
	/**
	 * Save the expand/collapse state of a folder
	 */
	saveState(folderSlug, isExpanded) {
		if (!window.localStorage) {
			return;
		}

		try {
			const states = this.getAllStates();
			states[folderSlug] = isExpanded;
			localStorage.setItem(STORAGE_KEY, JSON.stringify(states));
		} catch (error) {
			console.error('Failed to save folder state:', error);
		}
	}

	/**
	 * Get the state of a specific folder
	 * @param {string} folderSlug - Folder slug to check
	 * @returns {boolean} True if expanded, false if collapsed (defaults to true)
	 */
	getState(folderSlug) {
		const states = this.getAllStates();
		return states[folderSlug] ?? true; // Default to expanded
	}

	/**
	 * Get all folder states
	 * @returns {Object} Map of folderSlug -> isExpanded
	 */
	getAllStates() {
		if (!window.localStorage) {
			return {};
		}

		try {
			return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
		} catch {
			return {};
		}
	}

	/**
	 * Clear all folder states
	 */
	clearAll() {
		if (!window.localStorage) {
			return;
		}
		localStorage.removeItem(STORAGE_KEY);
	}
}
