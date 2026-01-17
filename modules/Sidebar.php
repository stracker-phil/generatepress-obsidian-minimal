<?php

namespace ObsidianMinimalTheme;

use WP_REST_Response;
use WP_REST_Request;
use WP_Post;

require_once 'Module.php';

class Sidebar extends Module {
	private const string REST_PATH = '/sidebar';

	private const string CACHE_KEY_DATA    = 'obsidian_sidebar_items';
	private const string CACHE_KEY_VERSION = 'obsidian_sidebar_items_version';

	private const string MENU_LOCATION = 'sidebar';

	protected function init_rest(): void {
		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_PATH, [
				'methods'             => 'GET',
				'callback'            => $this->rest_handler( ... ),
				'permission_callback' => '__return_true',
			]
		);
	}

	protected function init_theme(): void {
		register_nav_menu( self::MENU_LOCATION, __( 'Sidebar menu', 'gp-obsidian-minimal' ) );

		add_action( 'wp_update_nav_menu', $this->invalidate_cache( ... ) );
		add_action( 'wp_update_nav_menu_item', $this->invalidate_cache( ... ) );
		add_action( 'save_post', $this->invalidate_cache( ... ) );
		add_action( 'delete_post', $this->invalidate_cache( ... ) );
		add_action( 'wp_trash_post', $this->invalidate_cache( ... ) );
		add_action( 'untrash_post', $this->invalidate_cache( ... ) );
		add_action( 'created_term', $this->invalidate_cache( ... ) );
		add_action( 'edited_term', $this->invalidate_cache( ... ) );
		add_action( 'delete_term', $this->invalidate_cache( ... ) );
	}

	protected function init(): void {
		add_action( 'wp_enqueue_scripts', $this->frontend_assets( ... ) );
		add_action( 'wp_head', $this->inline_state_script( ... ), 1 );
		add_filter( 'body_class', $this->add_body_classes( ... ) );
	}

	private function frontend_assets(): void {
		$version = get_transient( self::CACHE_KEY_VERSION );

		if ( false === $version ) {
			$version = time();
			set_transient( self::CACHE_KEY_VERSION, $version, YEAR_IN_SECONDS );
		}

		$this->assets->add_module( 'obsidian-sidebar', 'init.js' );
		$this->assets->add_style( 'obsidian-sidebar', 'sidebar.css' );

		wp_localize_script( 'obsidian-sidebar', 'obsidianSidebarConfig', [
			'apiUrl'  => rest_url( self::REST_NAMESPACE . self::REST_PATH ),
			'version' => $version,
		] );
	}

	private function add_body_classes( array $classes ): array {
		$classes[] = 'sidebar-position-left';

		return $classes;
	}

	private function inline_state_script(): void {
		// Inline script to prevent CLS by setting sidebar state before first paint.
		// Reads localStorage and adds class to <html> if sidebar should be visible.
		?>
		<script>
			(function(){try{if(localStorage.getItem('obsidian-sidebar-visible')!=='false'&&innerWidth>1024)document.documentElement.classList.add('has-obsidian-sidebar')}catch(e){}})()
		</script>
		<?php
	}

	private function invalidate_cache( mixed $post_id = null ): void {
		$relevant_post_types = [ 'page', 'post', 'nav_menu_item' ];

		if ( $post_id && is_numeric( $post_id ) ) {
			$post_type = get_post_type( $post_id );

			if ( ! in_array( $post_type, $relevant_post_types, true ) ) {
				return;
			}
		}

		delete_transient( self::CACHE_KEY_DATA );
		delete_transient( self::CACHE_KEY_VERSION );
	}

	private function rest_handler( WP_REST_Request $request ): WP_REST_Response {
		$tree = $this->get_sidebar_items();

		$response = new WP_REST_Response( $tree );

		$response->header( 'Cache-Control', 'public, max-age=31536000, immutable' );

		$etag = md5( serialize( $tree ) );
		$response->header( 'ETag', $etag );

		$client_etag = $request->get_header( 'If-None-Match' );
		if ( $client_etag === $etag ) {
			return new WP_REST_Response( null, 304 );
		}

		return $response;
	}

	private function get_sidebar_items(): array {
		$tree = get_transient( self::CACHE_KEY_DATA );

		if ( is_array( $tree ) ) {
			return $tree;
		}

		$tree = [];

		$shortcuts = $this->get_shortcuts_folder();
		if ( $shortcuts ) {
			$tree[] = $shortcuts;
		}

		$categories = get_categories( [
			'taxonomy'   => 'category',
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => true,
		] );

		foreach ( $categories as $cat ) {
			$posts = get_posts( [
				'category'    => $cat->term_id,
				'numberposts' => - 1,
				'orderby'     => 'date',
				'order'       => 'DESC',
				'post_status' => 'publish',
			] );

			if ( empty( $posts ) ) {
				continue;
			}

			$tree[] = [
				'id'    => $cat->term_id,
				'name'  => $cat->name,
				'slug'  => $cat->slug,
				'count' => $cat->count,
				'posts' => array_map( static function ( $post ) {
					return [
						'id'        => $post->ID,
						'title'     => $post->post_title,
						'permalink' => get_permalink( $post->ID ),
						'date'      => $post->post_date,
					];
				}, $posts ),
			];
		}

		$tree = apply_filters( 'obsidian_sidebar_items', $tree );

		set_transient( self::CACHE_KEY_DATA, $tree, YEAR_IN_SECONDS );

		return $tree;
	}

	private function get_shortcuts_folder(): ?array {
		$locations = get_nav_menu_locations();

		if ( empty( $locations[ self::MENU_LOCATION ] ) ) {
			return null;
		}

		$menu_items = wp_get_nav_menu_items( $locations[ self::MENU_LOCATION ] );
		if ( ! $menu_items ) {
			return null;
		}

		// Filter top-level menu items only.
		$items = [];
		foreach ( $menu_items as $item ) {
			if ( (int) $item->menu_item_parent !== 0 ) {
				continue;
			}

			$items[] = [
				'id'        => 'menu-' . $item->ID,
				'title'     => $item->title,
				'permalink' => $item->url,
				'date'      => null,
			];
		}

		if ( empty( $items ) ) {
			return null;
		}

		/**
		 * Filter the shortcuts folder name in the sidebar, visible on the front-end.
		 *
		 * @param string $label The publicly visible label for the sidebar menu group.
		 */
		$folder_name = apply_filters( 'obsidian_sidebar_shortcuts_name', __( 'Shortcuts', 'gp-obsidian-minimal' ) );

		return [
			'id'    => 'shortcuts',
			'name'  => $folder_name,
			'slug'  => 'shortcuts',
			'count' => count( $items ),
			'posts' => $items,
		];
	}
}
