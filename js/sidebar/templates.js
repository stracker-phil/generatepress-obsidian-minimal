import { ICON_FILE, ICON_CHEVRON_DOWN } from './icons.js';

const escapeDiv = document.createElement('div');
const currentUrl = normalizeUrl(window.location.href);

function icon(content, className = '') {
	const classAttr = className ? ` class="${className}"` : '';
	return `<span class='tree-item-icon'${classAttr}>${content}</span>`;
}

function inner(content) {
	return `<span class='tree-item-inner'>${content}</span><span class='tree-item-spacer'></span>`;
}

function createTreeItemSelf(type, title, content, attributes = '', extraClasses = '') {
	const element = type === 'file' ? 'a' : 'div';
	const classAttr = `tree-item-self tree-type-${type}${extraClasses}`;
	return `<${element} title='${title}' class='${classAttr}'${attributes}>${content}</${element}>`;
}

function escape(text) {
	escapeDiv.textContent = text;
	return escapeDiv.innerHTML;
}

function normalizeUrl(url) {
	const data = new URL(url);
	return `//${data.hostname}/${data.pathname}`;
}

function isCurrentPage(itemLink) {
	return currentUrl === normalizeUrl(itemLink);
}

export const folderTemplate = (category, level = 0) => {
	const childrenHtml = category.posts
		.map(post => fileTemplate(post, level + 1))
		.join('');

	const title = escape(category.name);
	const selfContent = icon(
		ICON_CHEVRON_DOWN,
		'tree-collapse-icon',
	) + inner(title) + `<span class='tree-item-count'>${category.count}</span>`;

	const self = createTreeItemSelf(
		'folder',
		title,
		selfContent,
		`style="--level:${level}" role="button" tabindex="0" aria-expanded="true"`,
	);

	return `
		<div class='tree-item' data-folder='${escape(category.slug)}' style='--item-count:${category.count}'>
			${self}
			<div class='tree-item-children'>${childrenHtml}</div>
		</div>
	`;
};

export const fileTemplate = (post, level = 0) => {
	const activeClass = isCurrentPage(post.permalink) ? ' is-active' : '';
	const title = escape(post.title);
	const selfContent = icon(ICON_FILE) + inner(title);
	const attributes = ` href="${escape(post.permalink)}" data-post-id="${post.id}" style="--level:${level}"`;
	const self = createTreeItemSelf('file', title, selfContent, attributes, activeClass);

	return `<div class='tree-item'>${self}</div>`;
};
