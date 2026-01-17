export const ICON_CHEVRON_DOWN = `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='svg-icon'>
	<path d='m6 9 6 6 6-6'></path>
</svg>`;

export const ICON_FILE = `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='svg-icon'>
	<path d='M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z'></path>
	<path d='M14 2v5a1 1 0 0 0 1 1h5'></path>
</svg>`;

const ICON_SIDEBAR_LEFT = `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='svg-icon'>
	<rect x='1' y='2' width='22' height='20' rx='4'></rect>
	<rect x='4' y='5' width='2' height='14' rx='2' fill='currentColor'></rect>
</svg>`;

const ICON_SIDEBAR_RIGHT = `<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
	<rect x='1' y='2' width='22' height='20' rx='4'></rect>
	<rect x='4' y='5' width='2' height='14' rx='2' fill='currentColor'></rect>
</svg>`;

export function getSidebarIcon(position) {
	return position === 'left' ? ICON_SIDEBAR_LEFT : ICON_SIDEBAR_RIGHT;
}
