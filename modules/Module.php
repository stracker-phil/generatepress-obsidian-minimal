<?php

namespace ObsidianMinimalTheme;

require_once 'Assets.php';

abstract class Module {
	protected const string REST_NAMESPACE = 'obsidian/v1';

	protected Assets $assets;

	public static function inst() {
		static $instances = [];
		$key = static::class;

		if ( ! isset( $instances[ $key ] ) ) {
			$instances[ $key ] = new static();
		}

		return $instances[ $key ];
	}

	private function __construct() {
		$this->assets = Assets::inst();

		add_action( 'after_setup_theme', $this->init_theme( ... ) );
		add_action( 'rest_api_init', $this->init_rest( ... ) );
		add_action( 'wp', $this->init( ... ) );
		add_action( 'admin_init', $this->init_admin( ... ) );
	}

	/**
	 * Early entry point for theme setup (menus, theme support, etc.)
	 */
	protected function init_theme(): void {
	}

	/**
	 * Entry point for child classes.
	 */
	protected function init_rest(): void {
	}

	/**
	 * Entry point for child classes.
	 */
	protected function init(): void {
	}

	/**
	 * Entry point for child classes.
	 */
	protected function init_admin(): void {
	}
}
