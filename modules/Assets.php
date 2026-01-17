<?php

namespace ObsidianMinimalTheme;

/**
 * Manages theme assets (scripts, styles, modules)
 * Singleton class shared by all Module instances
 */
class Assets {
	private static ?Assets $instance = null;

	private static array $module_scripts = [];

	private string $theme_path;
	private string $theme_url;
	private array $modules = [];

	public static function inst(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->theme_path = trailingslashit( get_stylesheet_directory() );
		$this->theme_url  = trailingslashit( get_stylesheet_directory_uri() );

		add_action( 'wp_enqueue_scripts', $this->print_importmap( ... ), 9999 );
		add_filter( 'script_loader_tag', [ self::class, 'add_module_type' ], 10, 2 );
	}

	/**
	 * Enqueue a stylesheet
	 */
	public function add_style( string $handle, string $path, array $deps = [] ): void {
		wp_enqueue_style(
			$handle,
			$this->theme_url . 'css/' . $path,
			$deps,
			$this->get_asset_version( 'css/' . $path )
		);
	}

	/**
	 * Enqueue a script
	 */
	public function add_script( string $handle, string $path, array $deps = [] ): void {
		wp_enqueue_script(
			$handle,
			$this->theme_url . 'js/' . $path,
			$deps,
			$this->get_asset_version( 'js/' . $path ),
			true
		);
	}

	/**
	 * Enqueue a script as "module" with a list of import modules
	 */
	public function add_module( string $handle, string $path, array $imports = [] ): void {
		$this->add_script( $handle, $path );
		self::$module_scripts[] = $handle;

		foreach ( $imports as $import => $import_path ) {
			if ( is_numeric( $import ) ) {
				$import = $import_path;
			}
			$this->modules[ $import ] = $import_path;
		}
	}

	/**
	 * Print import map for ES6 modules
	 */
	private function print_importmap(): void {
		if ( ! $this->modules ) {
			return;
		}

		$map = [ 'imports' => [] ];
		foreach ( $this->modules as $module => $path ) {
			$map['imports'][ $module ] = $this->theme_url . 'js/' . $path . '.js';
		}

		printf( '<script type="importmap">%s</script>', wp_json_encode( $map ) );
	}

	/**
	 * Filter callback to add type="module" attribute to module scripts
	 */
	public static function add_module_type( string $tag, string $handle ): string {
		if ( in_array( $handle, self::$module_scripts, true ) ) {
			return str_replace( ' src=', ' type="module" src=', $tag );
		}

		return $tag;
	}

	/**
	 * Get asset version based on file modification time
	 */
	private function get_asset_version( string $path ): string {
		$full_path = $this->theme_path . $path;
		if ( file_exists( $full_path ) ) {
			return filemtime( $full_path );
		}

		return '';
	}
}
