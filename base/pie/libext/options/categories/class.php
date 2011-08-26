<?php
/**
 * PIE API: option extensions, categories class file
 *
 * @author Marshall Sorenson <marshall.sorenson@gmail.com>
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2010 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package PIE
 * @subpackage options-ext
 * @since 1.0
 */

/**
 * Categories option
 *
 * @package PIE
 * @subpackage options-ext
 */
class Pie_Easy_Exts_Option_Categories
	extends Pie_Easy_Options_Option
{
	/**
	 * Render category checkboxes
	 */
	public function render_field()
	{
		$args = array(
			'show_option_all'		=> false,
			'orderby'				=> 'name',
			'order'					=> 'ASC',
			'show_last_updated'		=> false,
			'style'					=> false,
			'show_count'			=> false,
			'hide_empty'			=> false,
			'use_desc_for_title'	=> false,
			'child_of'				=> false,
			'feed'					=> false,
			'feed_type'				=> false,
			'feed_image'			=> false,
			'exclude'				=> false,
			'exclude_tree'			=> false,
			'include'				=> false,
			'hierarchical'			=> false,
			'title_li'				=> __( 'Categories' ),
			'number'				=> null,
			'echo'					=> true,
			'depth'					=> false,
			'current_category'		=> false,
			'pad_counts'			=> false,
			'taxonomy'				=> 'category',
			'walker'				=> new Pie_Easy_Options_Walker_Category(),
			'pie_easy_option'		=> $this );

		// render div wrapper if applicable
		if ( $this->field_id ) { ?>
			<div id="<?php print $this->field_id ?>"><?php
		}

		// call the WordPress function
		wp_list_categories( $args );

		// close div wrapper if applicable
		if ( $this->field_id ) { ?>
			</div><?php
		}
	}
}

?>