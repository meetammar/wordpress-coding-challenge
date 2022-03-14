<?php
/**
 * Block class.
 *
 * @package SiteCounts
 */

namespace XWP\SiteCounts;

use WP_Block;
use WP_Query;

/**
 * The Site Counts dynamic block.
 *
 * Registers and renders the dynamic block.
 */
class Block {

	/**
	 * The Plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Instantiates the class.
	 *
	 * @param Plugin $plugin The plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Adds the action to register the block.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Registers the block.
	 */
	public function register_block() {
		register_block_type_from_metadata(
			$this->plugin->dir(),
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	/**
	 * Renders the block.
	 *
	 * @param array    $attributes The attributes for the block.
	 * @param string   $content    The block content, if any.
	 * @param WP_Block $block      The instance of this block.
	 * @return string The markup of the block.
	 */
	public function render_callback( $attributes, $content, $block ) {

		$post_types = get_post_types( [ 'public' => true ] );
		$class_name = isset( $attributes['className'] ) ? $attributes['className'] : '';
		ob_start();
		?>
		<div class="<?php echo esc_attr( $class_name ); ?>">
			<h2><?php _e( 'Post Counts', 'site-counts' ); ?></h2>
			<ul>
				<?php
				if ( ! empty( $post_types ) ) :
					foreach ( $post_types as $post_type_slug ) :
						$post_type_object = get_post_type_object( $post_type_slug );
						$post_count       = count(
							get_posts(
								[
									'post_type'      => $post_type_slug,
									'posts_per_page' => -1,
								]
							)
						);
						?>
						<li>
							<?php
							if ( $post_count > 0 ) {
								echo wp_sprintf(
									/* translators: 1: Total count, 2:3: String text for Post/Taxonomy */
									_n(
										'There is only %1$l %2$s.',
										'There are %1$l %3$s.',
										esc_attr( $post_count ),
										'site-counts'
									),
									esc_attr( number_format_i18n( $post_count ) ),
									esc_attr( $post_type_object->labels->singular_name ),
									esc_attr( $post_type_object->labels->name ),
								);
							} else {
								echo wp_sprintf(
									/* translators: %s: String text for Post/Taxonomy */
									esc_attr__( 'There are no %s', 'site-counts' ),
									esc_attr( $post_type_object->labels->name )
								);
							}
							?>
						</li>
						<?php
					endforeach;
				endif;
				?>
			</ul>
			<p>
				<?php
				echo wp_sprintf(
					esc_html__( 'The current post ID is %l.', 'site-counts' ),
					get_the_ID()
				);
				?>
			</p>

			<?php
			$query = new WP_Query(
				[
					'post_type'      => 'post',
					'post_status'    => 'any',
					'tag'            => 'foo',
					'category_name'  => 'baz',
					'post__not_in'   => [ get_the_ID() ],
					'posts_per_page' => 5,
				]
			);

			if ( $query->have_posts() ) :
				?>
				<h2><?php _e( '5 posts with the tag of foo and the category of baz', 'site-counts' ); ?></h2>
				<ul>
					<?php
					foreach ( $query->posts as $post ) :
						echo '<li>' . esc_html( $post->post_title ) . '</li>';
					endforeach;
					?>
				</ul>
				<?php
			endif;
			?>
		</div>
		<?php

		return ob_get_clean();
	}
}
