<?php

namespace ObsidianMinimalTheme;

require_once 'Module.php';

class Footer extends Module {
	protected function init(): void {
		remove_all_filters( 'generate_copyright' );

		add_filter( 'generate_copyright', $this->generate_footer( ... ) );
	}

	/**
	 * Customize the footer text, with the main objective to insert a link
	 * pointing to the impressum on every page.
	 */
	private function generate_footer(): string {
		$segments = [];

		// Copyright segment.
		$segments[] = [
			'class'   => 'footer-copyright',
			'content' => sprintf(
				'&copy; 2017-%d %s',
				date( 'Y' ),
				esc_html( get_bloginfo( 'name' ) )
			),
		];

		// Privacy policy (or impressum) segment.
		$policy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );

		if ( ! empty( $policy_page_id ) && get_post_status( $policy_page_id ) === 'publish' ) {
			$segments[] = [
				'class'   => 'footer-legal',
				'content' => sprintf(
					'<a href="%s" rel="noopener nofollow">%s</a>',
					get_permalink( $policy_page_id ),
					esc_html( get_the_title( $policy_page_id ) )
				),
			];
		}

		// A custom footer string (can contain HTML).
		$segments[] = [
			'class'   => 'footer-notice',
			'tag'     => 'div',
			'content' => apply_filters( 'footer_segment_notice', '' ),
		];

		// Build the final footer output.
		$segments          = apply_filters( 'footer_segments', $segments );
		$filtered_segments = array_filter( $segments, static fn( $item ) => is_array( $item ) && ! empty( $item['content'] ) );
		$final_segments    = array_map(
			static fn( $item ) => sprintf(
				'<%1$s class="footer-segment %2$s">%3$s</%1$s>',
				esc_attr( $item['tag'] ?? 'span' ),
				esc_attr( $item['class'] ?? '' ),
				wp_kses_post( $item['content'] )
			),
			$filtered_segments
		);

		return implode( ' ', $final_segments );
	}
}
