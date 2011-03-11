<?php
/**
 * PIE scheme helper class file
 *
 * @author Marshall Sorenson <marshall.sorenson@gmail.com>
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2010 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package pie
 * @subpackage scheme
 * @since 1.0
 */

Pie_Easy_Loader::load( 'files' );

/**
 * Make Scheming Easy
 */
final class Pie_Easy_Scheme
{
	/**
	 * Name of the assets directory
	 */
	const DIR_ASSETS = 'assets';

	/**
	 * Name of the css directory
	 */
	const DIR_CSS = 'css';

	/**
	 * Name of the images directory
	 */
	const DIR_IMAGES = 'images';

	/**
	 * Name of the images directory
	 */
	const DIR_JS = 'js';

	/**
	 * Name of the docs directory (under config)
	 */
	const DIR_DOCS = 'docs';
	
	/**
	 * ini directives
	 */
	const DIRECTIVE_PARENT_THEME = 'parent_theme';
	const DIRECTIVE_STYLE_DEFS = 'style';
	const DIRECTIVE_STYLE_ACTS = 'style_actions';
	const DIRECTIVE_STYLE_CONDS = 'style_conditions';
	const DIRECTIVE_ADVANCED = 'advanced';
	const DIRECTIVE_OPT_SAVE_SINGLE = 'options_save_single';

	/**
	 * Singleton instance
	 * 
	 * @var Pie_Easy_Scheme
	 */
	static private $instance;

	/**
	 * Relative path to the config dir relative to the theme's template path
	 *
	 * @var string
	 */
	private $config_dir;

	/**
	 * Name of the configuration ini file that is preferred by the API
	 *
	 * @var string
	 */
	private $config_file;

	/**
	 * Theme stack
	 *
	 * @var Pie_Easy_Stack
	 */
	private $themes;

	/**
	 * @var Pie_Easy_Map Option directives
	 */
	private $directives;

	/**
	 * Constructor
	 * 
	 * this is a singleton
	 */
	private function __construct()
	{
		// initialize themes map
		$this->themes = new Pie_Easy_Stack();
		$this->directives = new Pie_Easy_Map();
	}

	/**
	 * Return the singleton instance
	 *
	 * @return Pie_Easy_Scheme
	 */
	static public function instance()
	{
		if ( !self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * One time initialization helper
	 *
	 * @param string $config_dir
	 * @param string $config_file
	 * @return boolean
	 */
	public function init( $config_dir, $config_file )
	{
		// setup config
		$this->set_config_dir( $config_dir );
		$this->set_config_file( $config_file );

		// load it
		return $this->load();
	}

	/**
	 * Set the name of the dir under the child themes where the config file lives
	 *
	 * @param string $dir_name
	 * @return boolean
	 */
	public function set_config_dir( $dir_name )
	{
		if ( empty( $this->config_dir ) ) {
			$this->config_dir = $dir_name;
			return true;
		}

		return false;
	}

	/**
	 * Set the name of the config file that your API uses
	 *
	 * @param string $file_name
	 * @return boolean
	 */
	public function set_config_file( $file_name )
	{
		if ( empty( $this->config_file ) ) {
			$this->config_file = $file_name;
			return true;
		}

		return false;
	}

	/**
	 * Has a directive?
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function has_directive( $name )
	{
		// check for theme directive
		return $this->directives->contains( $name );
	}

	/**
	 * Get a directive
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get_directive( $name )
	{
		// check for existing map of theme directives
		if ( $this->has_directive( $name ) ) {
			// use existing directive map
			$directive_map = $this->get_directive_map( $name );
			// check for directive according to theme stack from TOP DOWN
			foreach ( $this->themes->to_array(true) as $theme ) {
				// does theme have this directive set?
				if ( $directive_map->contains( $theme ) ) {
					return $directive_map->item_at($theme)->value;
				}
			}
		}

		// directive not set
		return null;
	}

	/**
	 * Get a directive's themes map
	 *
	 * @param string $name
	 * @return Pie_Easy_Map|null
	 */
	private function get_directive_map( $name )
	{
		if ( $this->has_directive( $name ) ) {
			return $this->directives->item_at( $name );
		}

		// directive not set
		return null;
	}

	/**
	 * Set a directive
	 *
	 * @param string $theme
	 * @param string $name
	 * @param mixed $value
	 * @param boolean $read_only
	 */
	private function set_directive( $theme, $name, $value, $read_only = null )
	{
		// convert arrays to maps
		if ( is_array( $value ) ) {
			$value = new Pie_Easy_Map( $value, $read_only );
		}

		// check for existing map of theme directives
		if ( $this->has_directive( $name ) ) {
			// use existing directive map
			$directive_map = $this->get_directive_map( $name );
		} else {
			// create and add new map
			$directive_map = new Pie_Easy_Map();
			$this->directives->add( $name, $directive_map );
		}

		// check for existing directive for given theme
		if ( $directive_map->contains( $theme ) ) {
			return $directive_map->item_at($theme)->set_value( $value );
		} else {
			// create new directive
			$directive = new Pie_Easy_Scheme_Directive( $name, $value, $read_only );
			// add it to directive map
			return $directive_map->add( $theme, $directive );
		}
	}

	/**
	 * Add template filters
	 */
	private function add_filters()
	{
		// only add filters if there is at least one theme
		if ( count( $this->themes ) ) {

			// template filter callback
			$filter = array( $this, 'filter_template' );

			// add filters
			add_filter( '404_template', $filter );
			add_filter( 'search_template', $filter );
			add_filter( 'taxonomy_template', $filter );
			add_filter( 'front_page_template', $filter );
			add_filter( 'home_template', $filter );
			add_filter( 'attachment_template', $filter );
			add_filter( 'single_template', $filter );
			add_filter( 'page_template', $filter );
			add_filter( 'category_template', $filter );
			add_filter( 'tag_template', $filter );
			add_filter( 'author_template', $filter );
			add_filter( 'date_template', $filter );
			add_filter( 'archive_template', $filter );
			add_filter( 'comments_popup_template', $filter );
			add_filter( 'paged_template', $filter );
			add_filter( 'index_template', $filter );
			add_filter( 'comments_template', $filter );

		}
	}

	/**
	 * Load the scheme, using a theme as the starting point for the stack
	 *
	 * @param string $theme Theme's *directory name*
	 * @return boolean
	 */
	public function load( $theme = null )
	{
		if ( empty( $theme ) ) {
			$theme = $this->active_theme();
		}

		// paths to files
		$ini_file = $this->theme_file( $theme, $this->config_dir, $this->config_file . '.ini' );

		// does ini file exist?
		if ( is_readable( $ini_file ) ) {

			// parse it
			$ini = parse_ini_file( $ini_file, true );

			// make sure we got something
			if ( $ini !== false ) {

				// parent theme?
				$parent_theme =
					isset( $ini[self::DIRECTIVE_PARENT_THEME] )
						? $ini[self::DIRECTIVE_PARENT_THEME]
						: false;

				// recurse up the theme stack if necessary
				if ( $parent_theme ) {
					// load it
					$this->load( $parent_theme );
				}

				// push onto the stack AFTER recursion
				$this->themes->push( $theme );

				// loop through directives and set them
				foreach ( $ini as $name => $value ) {
					if ( $name == self::DIRECTIVE_ADVANCED ) {
						if ( is_array( $value ) ) {
							foreach ( $value as $name_adv => $value_adv ) {
								$this->set_directive( $theme, $name_adv, $value_adv, true );
							}
						}
						continue;
					} else {
						$this->set_directive( $theme, $name, $value, true );
					}
				}

			} else {
				throw new Exception( 'Failed to parse theme ini file: ' . $ini_file );
			}
		}

		// try to load additional functions files after WP theme setup
		add_action( 'after_setup_theme', array($this, 'load_functions') );

		// add filters
		$this->add_filters();
	}

	/**
	 * Try to load functions file for themes in stack
	 */
	public function load_functions()
	{
		// loop through theme stack
		foreach ( $this->themes->to_array() as $theme  ) {
			// load functions file if it exists
			include_once $this->theme_file( $theme, 'functions.php' );
		}
	}

	/**
	 * Load options for a theme
	 *
	 * @param Pie_Easy_Options_Registry $registry
	 * @param string $ini_file_name
	 * @return boolean
	 */
	public function load_options( Pie_Easy_Options_Registry $registry, $ini_file_name = 'options' )
	{
		// loop through entire theme stack BOTTOM UP and try to load options
		foreach( $this->themes->to_array() as $theme ) {

			// path to options ini
			$options_ini = $this->theme_file( $theme, $this->config_dir, $ini_file_name . '.ini' );

			// load the option config if it exists
			if ( is_readable( $options_ini ) ) {
				$registry->load_config_file( $options_ini, $theme );
			}

		}

		return true;
	}

	/**
	 * If template exists in scheme, return it, otherwise return the original template
	 *
	 * @param string $template
	 * @return string
	 */
	function filter_template( $template )
	{
		// fall back to index
		if ( empty( $template ) ) {
			$template = 'index.php';
		}
	
		// see if it exists in the scheme
		$scheme_template = $this->locate_template( array( basename( $template ) ) );

		// return scheme template?
		if ( $scheme_template ) {
			return $scheme_template;
		} else {
			return $template;
		}
	}

	/**
	 * Find and optionally load template(s) if it exists anywhere in the scheme
	 *
	 * @param string|array $template_names
	 * @param boolean $load Auto load template if set to true
	 * @return string
	 */
	public function locate_template( $template_names, $load = false )
	{
		// must have at least one theme to search
		if ( count( $this->themes ) ) {

			// convert string arg to array
			if ( !is_array( $template_names ) ) {
				$template_names = array( $template_names );
			}

			// loop through all templates
			foreach ( $template_names as $template_name ) {

				// loop through the entire theme stack FROM TOP DOWN
				foreach ( $this->themes->to_array(true) as $theme ) {

					// prepend all template names with theme dir
					$located_template = $this->theme_file( $theme, $template_name );

					// does it exist?
					if ( file_exists( $located_template ) ) {
						// load it?
						if ($load) {
							load_template( $located_template );
						}
						// return the located template path
						return $located_template;
					}
				}
			}
		}

		// didn't find a template
		return '';
	}

	/**
	 * Return the name of the active theme
	 * 
	 * @return string
	 */
	private function active_theme()
	{
		return get_stylesheet();
	}

	/**
	 * Return path to a theme directory
	 *
	 * @param string $theme
	 * @return string
	 */
	public function theme_dir( $theme )
	{
		return get_theme_root() . DIRECTORY_SEPARATOR . $theme;
	}

	/**
	 * Return URL to a theme directory
	 *
	 * @param string $theme
	 * @return string
	 */
	public function theme_dir_url( $theme )
	{
		return get_theme_root_uri() . '/' . $theme;
	}
	
	/**
	 * Return array of all theme root directory paths
	 *
	 * @param string $file_names,...
	 * @return array
	 */
	public function theme_dirs( $file_names = null )
	{
		// did we get an array as the first arg?
		if ( !is_array( $file_names ) ) {
			// nope, get all args
			$file_names = func_get_args();
		}

		// paths to return
		$paths = array();

		foreach ( $this->themes->to_array(true) as $theme ) {
			$paths[] = $this->theme_file( $theme, $file_names );
		}

		return $paths;
	}

	/**
	 * Return array of all theme config dirs
	 *
	 * @return array
	 */
	public function theme_config_dirs()
	{
		return $this->theme_dirs( $this->config_dir );
	}

	/**
	 * Return array of all theme config dirs
	 *
	 * @return array
	 */
	public function theme_documentation_dirs()
	{
		return $this->theme_dirs( $this->config_dir, self::DIR_DOCS );
	}

	/**
	 * Return path to a theme file
	 *
	 * @param string $theme
	 * @param string $file_names,...
	 */
	public function theme_file( $theme, $file_names )
	{
		// did we get an array as the second arg?
		if ( !is_array( $file_names ) ) {
			// nope, get all args except the first
			$file_names = func_get_args();
			array_shift($file_names);
		}

		return $this->theme_dir( $theme ) . Pie_Easy_Files::path_build( $file_names );
	}

	/**
	 * Return URL to a theme file
	 *
	 * @param string $theme
	 * @param string $file_names,...
	 */
	public function theme_file_url( $theme, $file_names )
	{
		// get all args except the first
		$file_names = func_get_args();
		array_shift($file_names);

		return $this->theme_dir_url( $theme ) . '/' . implode( '/', $file_names );
	}

	/**
	 * Locate a theme file, giving priority to top themes in the stack
	 *
	 * @param string $file_names,... The file names that make up the RELATIVE path to the theme root
	 * @return string|false
	 */
	public function locate_file( $file_names )
	{
		// did we get an array as the first arg?
		if ( !is_array( $file_names ) ) {
			// nope, get all args
			$file_names = func_get_args();
		}

		// file path to be located
		$locate_names = array();

		// split all strings in case thy contain a static directory separator
		foreach ( $file_names as $file_name ) {
			// split it
			$splits = Pie_Easy_Files::path_split( $file_name );
			// append to array
			foreach ( $splits as $split ) {
				$locate_names[] = $split;
			}
		}

		// loop through stack TOP DOWN
		foreach ( $this->themes->to_array(true) as $theme ) {

			// path to stackfile
			$stack_file =
				$this->theme_dir( $theme ) . Pie_Easy_Files::path_build( $locate_names );

			// does stack file exist?
			if ( is_readable( $stack_file ) ) {
				return $stack_file;
			}
		}

		return false;
	}

	/**
	 * Locate a config file, giving priority to lower themes in the stack
	 *
	 * @param string $file_names,... The file names that make up the RELATIVE path to the theme config root
	 * @return string|false
	 */
	public function locate_config_file( $file_names )
	{
		// get all args
		$file_names = func_get_args();

		// prepend file names with path to config directory
		array_unshift( $file_names, $this->config_dir );

		// call the generic locator
		return $this->locate_file( $file_names );
	}

	/**
	 * Assets directory path
	 *
	 * @param string $theme
	 * @return string
	 */
	private function assets_dir( $theme = null )
	{
		if ( empty( $theme ) ) {
			$theme = $this->active_theme();
		}

		return
			get_theme_root() .
			DIRECTORY_SEPARATOR . $theme .
			DIRECTORY_SEPARATOR . self::DIR_ASSETS;
	}

	/**
	 * Assets directory URL
	 *
	 * @param string $theme
	 * @return string
	 */
	private function assets_url( $theme = null )
	{
		if ( empty( $theme ) ) {
			$theme = $this->active_theme();
		}

		return
			get_theme_root_uri() .
			'/' . $theme .
			'/' . self::DIR_ASSETS;
	}

	/**
	 * CSS directory path
	 *
	 * @param string $theme
	 * @return string
	 */
	public function css_dir( $theme = null )
	{
		return $this->assets_dir( $theme ) . DIRECTORY_SEPARATOR . self::DIR_CSS;
	}

	/**
	 * CSS directory URL
	 *
	 * @param string $theme
	 * @return string
	 */
	public function css_url( $theme = null )
	{
		return $this->assets_url( $theme ) . '/' . self::DIR_CSS;
	}

	/**
	 * JS directory path
	 *
	 * @param string $theme
	 * @return string
	 */
	public function js_dir( $theme = null )
	{
		return $this->assets_dir( $theme ) . DIRECTORY_SEPARATOR . self::DIR_JS;
	}

	/**
	 * JS directory URL
	 *
	 * @param string $theme
	 * @return string
	 */
	public function js_url( $theme = null )
	{
		return $this->assets_url( $theme ) . '/' . self::DIR_JS;
	}

	/**
	 * Images directory path
	 *
	 * @param string $theme
	 * @return string
	 */
	public function images_dir( $theme = null )
	{
		return $this->assets_dir( $theme ) . DIRECTORY_SEPARATOR . self::DIR_IMAGES;
	}

	/**
	 * Images directory URL
	 *
	 * @param string $theme
	 * @return string
	 */
	public function images_url( $theme = null )
	{
		return $this->assets_url( $theme ) . '/' . self::DIR_IMAGES;
	}

	/**
	 * Look for a header in the scheme stack
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function get_header( $name = null )
	{
		$templates = array();

		if ( isset($name) )
			$templates[] = "header-{$name}.php";

		$templates[] = "header.php";

		$located_template = $this->locate_template( $templates );

		if ( $located_template ) {
			do_action( 'get_header', $name );
			return load_template( $located_template );
		} else {
			return get_header( $name );
		}
	}

	/**
	 * Look for a footer in the scheme stack
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function get_footer( $name = null )
	{
		$templates = array();

		if ( isset($name) )
			$templates[] = "footer-{$name}.php";

		$templates[] = "footer.php";

		$located_template = $this->locate_template( $templates );

		if ( $located_template ) {
			do_action( 'get_footer', $name );
			return load_template( $located_template );
		} else {
			return get_footer( $name );
		}
	}

	/**
	 * Look for a sidebar in the scheme stack
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function get_sidebar( $name = null )
	{
		$templates = array();

		if ( isset($name) )
			$templates[] = "sidebar-{$name}.php";

		$templates[] = "sidebar.php";

		$located_template = $this->locate_template( $templates );

		if ( $located_template ) {
			do_action( 'get_sidebar', $name );
			return load_template( $located_template );
		} else {
			return get_sidebar( $name );
		}
	}

	/**
	 * Look for a template part in the scheme stack
	 *
	 * @param string $slug The slug name for the generic template.
	 * @param string $name The name of the specialised template.
	 */
	function get_template_part( $slug, $name = null )
	{
		$templates = array();
		if ( isset($name) )
			$templates[] = "{$slug}-{$name}.php";

		$templates[] = "{$slug}.php";
		
		$located_template = $this->locate_template( $templates );
		
		if ( $located_template ) {
			do_action( "get_template_part_{$slug}", $slug, $name );
			load_template( $located_template, false );
		} else {
			get_template_part( $slug, $name );
		}
	}

	/**
	 * Look for a search form in the scheme stack
	 *
	 * @param string $slug The slug name for the generic template.
	 * @param string $name The name of the specialised template.
	 */
	function get_search_form( $echo = true )
	{
		$located_template = $this->locate_template( 'searchform.php' );

		if ( $located_template ) {
			do_action( 'get_search_form' );
			load_template( $located_template, false );
		} else {
			return get_search_form( $echo );
		}
	}
}

?>