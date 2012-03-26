<?php
/**
 * ICE API: option extensions, post class file
 *
 * @author Marshall Sorenson <marshall@presscrew.com>
 * @link http://infinity.presscrew.com/
 * @copyright Copyright (C) 2010-2011 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package ICE-extensions
 * @subpackage options
 * @since 1.0
 */

ICE_Loader::load_ext( 'options/select' );

/**
 * Post option
 *
 * @package ICE-extensions
 * @subpackage options
 */
class ICE_Exts_Options_Post
	extends ICE_Exts_Options_Select
		implements ICE_Options_Option_Auto_Field
{
	/**
	 */
	public function load_field_options()
	{
		// get all posts
		$posts = get_posts();

		// field options
		$options = array();

		// build of options array
		foreach ( $posts as $post ) {
			$options[$post->ID] = apply_filters( 'the_title', $post->post_title, $post->ID );
		}

		return $options;
	}
}

?>