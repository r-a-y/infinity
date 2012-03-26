<?php
/**
 * ICE API: feature extensions, default feature class file
 *
 * @author Marshall Sorenson <marshall@presscrew.com>
 * @link http://infinity.presscrew.com/
 * @copyright Copyright (C) 2010-2011 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package ICE-extensions
 * @subpackage features
 * @since 1.0
 */

ICE_Loader::load( 'ui/style' );

/**
 * Default feature
 *
 * Use this when your feature is very simple or just a container for functionality
 *
 * @package ICE-extensions
 * @subpackage features
 */
class ICE_Exts_Features_Default
	extends ICE_Features_Feature
{
	// nothing special here
}

?>