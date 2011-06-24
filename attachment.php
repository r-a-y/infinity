<?php
/**
 * Infinity Theme: attachment template
 *
 * @author Bowe Frankema <bowromir@gmail.com>
 * @link http://bp-tricks.com/
 * @copyright Copyright (C) 2010 Bowe Frankema
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package infinity
 * @subpackage templates
 * @since 1.0
 */

	infinity_get_header();
?>
	<div id="content">
		<?php
			do_action( 'before_content' );
			do_action( 'before_attachment' );
		?>
		<div class="page" id="single-attachment">
			<?php
				infinity_get_template_part( 'loop', 'attachment' );
			?>
		</div>
		<?php
			do_action( 'after_attachment' );
			do_action( 'after_content' );
		?>
	</div>
<?php
	infinity_get_sidebar();
	infinity_get_footer();
?>
