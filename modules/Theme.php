<?php

namespace ObsidianMinimalTheme;

require_once 'Module.php';

class Theme extends Module {
	protected function init(): void {
		add_action( 'wp_enqueue_scripts', $this->frontend_theme( ... ) );
		add_action( 'after_setup_theme', $this->editor_styles( ... ) );
		add_action( 'generate_before_entry_title', $this->post_meta_content( ... ) );

		remove_action( 'generate_after_entry_content', 'generate_footer_meta' );
		remove_action( 'generate_after_entry_title', 'generate_post_meta' );
	}

	private function frontend_theme(): void {
		$this->assets->add_style( 'website-theme', 'theme.css' );
	}

	private function editor_styles(): void {
		add_editor_style( 'theme.css' );
	}

	private function post_meta_content(): void {
		?>
		<div class="entry-meta">
			<?php $this->post_meta_content_row( 'Published on', 'date' ); ?>
		</div>
		<?php
	}

	private function post_meta_content_row( string $label, string $item ): void {
		?>
		<div class="entry-meta-row <?php echo esc_attr( $item ); ?>">
			<span class="entry-meta-label">
				<?php echo esc_html( $label ); ?>
			</span>
			<span class="entry-meta-value">
				<?php generate_do_post_meta_item( $item ); ?>
			</span>
		</div>
		<?php
	}
}
