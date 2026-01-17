<?php

namespace ObsidianMinimalTheme;

require_once 'Module.php';

/**
 * Renders the main navigation as an Obsidian-style tab bar.
 *
 * Menu items are displayed as "open note tabs" to increase the impression of
 * browsing an Obsidian vault. If the current page is not in the menu, it's
 * appended as the last tab.
 */
class TabBar extends Module {
	protected function init(): void {
		add_action( 'wp_enqueue_scripts', $this->frontend_assets( ... ) );

		remove_all_actions( 'generate_header' );

		add_action( 'generate_header', $this->render_tabbar( ... ), 5 );
	}

	private function frontend_assets(): void {
		$this->assets->add_style( 'obsidian-tabbar', 'tabbar.css' );
	}

	private function render_tabbar(): void {
		$tabs = $this->get_tabs();
		?>
		<nav
			class="tab-header-container" role="navigation"
			aria-label="<?php esc_attr_e( 'Primary', 'gp-obsidian-minimal' ); ?>"
		>
			<ul class="tab-header-container-inner">
				<?php
				foreach ( $tabs as $tab ) {
					echo $this->render_tab_item( $tab['title'], $tab['url'], $tab['active'] );
				}
				?>
			</ul>
			<span class='tab-header-spacer'></span>
		</nav>
		<?php
	}

	private function render_tab_item( string $title, string $url, bool $is_active ): string {
		if ( $is_active ) {
			return sprintf(
				'<li class="tab-item is-active-tab">
					<span class="tab-item-link" title="%1$s"><span class="tab-item-title">%1$s</span></span>
				</li>',
				esc_html( $title )
			);
		}

		return sprintf(
			'<li class="tab-item">
				<a class="tab-item-link" href="%1$s" title="%2$s"><span class="tab-item-title">%2$s</span></a>
			</li>',
			esc_url( $url ),
			esc_html( $title )
		);
	}

	private function get_tabs(): array {
		$tabs            = [];
		$current_url     = $this->get_current_url();
		$current_in_menu = false;

		$locations = get_nav_menu_locations();
		if ( empty( $locations['primary'] ) ) {
			return $this->maybe_add_current_page( $tabs, $current_url, $current_in_menu );
		}

		$menu_items = wp_get_nav_menu_items( $locations['primary'] );
		if ( ! $menu_items ) {
			return $this->maybe_add_current_page( $tabs, $current_url, $current_in_menu );
		}

		foreach ( $menu_items as $item ) {
			if ( (int) $item->menu_item_parent !== 0 ) {
				continue;
			}

			$is_active = $this->is_item_active( $item, $current_url );

			if ( $is_active ) {
				$current_in_menu = true;
			}

			$tabs[] = [
				'title'  => $item->title,
				'url'    => $item->url,
				'active' => $is_active,
			];
		}

		return $this->maybe_add_current_page( $tabs, $current_url, $current_in_menu );
	}

	private function is_item_active( object $item, string $current_url ): bool {
		$item_url = trailingslashit( $item->url );

		if ( $item_url === $current_url ) {
			return true;
		}

		if ( $item->object_id && is_singular() ) {
			return (int) $item->object_id === get_queried_object_id();
		}

		return false;
	}

	private function maybe_add_current_page( array $tabs, string $current_url, bool $current_in_menu ): array {
		if ( $current_in_menu ) {
			return $tabs;
		}

		$title = $this->get_current_page_title();
		if ( ! $title ) {
			return $tabs;
		}

		$tabs[] = [
			'title'  => $title,
			'url'    => $current_url,
			'active' => true,
		];

		return $tabs;
	}

	private function get_current_url(): string {
		global $wp;

		return trailingslashit( home_url( $wp->request ) );
	}

	private function get_current_page_title(): string {
		if ( is_singular() ) {
			return get_the_title();
		}

		if ( is_home() && ! is_front_page() ) {
			return get_the_title( get_option( 'page_for_posts' ) );
		}

		if ( is_category() || is_tag() || is_tax() ) {
			return single_term_title( '', false ) ?: '';
		}

		if ( is_archive() ) {
			return get_the_archive_title();
		}

		if ( is_search() ) {
			return sprintf( __( 'Search: %s', 'gp-obsidian-minimal' ), get_search_query() );
		}

		if ( is_404() ) {
			return __( 'Not Found', 'gp-obsidian-minimal' );
		}

		return '';
	}
}
