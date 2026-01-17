<?php

namespace ObsidianMinimalTheme;

require_once 'Module.php';

class Theme extends Module {
	protected function init(): void {
		add_action( 'wp_enqueue_scripts', $this->frontend_theme( ... ) );
		add_action( 'after_setup_theme', $this->editor_styles( ... ) );

		remove_action( 'generate_after_entry_content', 'generate_footer_meta' );
		remove_action( 'generate_after_entry_title', 'generate_post_meta' );
	}

	private function frontend_theme(): void {
		$this->assets->add_style( 'website-theme', 'theme.css' );
	}

	private function editor_styles(): void {
		add_editor_style( 'theme.css' );
	}
}
