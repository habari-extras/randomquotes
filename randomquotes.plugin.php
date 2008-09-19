<?php

/**
 * Random Quotes plugin for Habari 0.5+
 * 
 * Usage: <?php $theme->randomquote(); ?>
 *
 * @package randomquotes
 */

class RandomQuotes extends Plugin
{
	const VERSION = '0.1';
	const OPTION_NAME = 'randomquotes__filename';

	/**
	 * Returns information about this plugin
	 *
	 * @return array Plugin info array
	 **/
	public function info()
	{
		return array (
			'name' => 'Random Quotes',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'version' => self::VERSION,
			'description' => 'Outputs a random quote from XML files.',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Random Quotes', '9de12fcf-6c5c-43ad-8d94-c9eb398034e8', $this->info->version );
	}

	/**
	 * Outputs the options form on the plugin page.
	 **/
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$form = new FormUI( 'randomquotes' );
			$control = $form->append('select', 'control', self::OPTION_NAME, _t( 'Quotations file' ) );
			foreach( $this->get_all_filenames() as $filename => $file ) {
				$control->options[$filename] = $file->info->name . ": " . $file->info->description; 
			}
			$control->add_validator( 'validate_required' );
			$form->append( 'submit', 'save', _t( 'Save' ) );
			$form->out();
		}
	}

	/**
	 * Outputs the "configure" button on the plugin page.
	 **/
	public function filter_plugin_config( $actions, $plugin_id ) {
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}

	/**
	* get the absolute path to the smilies folder for given package name.
	*
	* @param string $package_name the smilies package name
	**/
	private function get_path()
	{
		return dirname( $this->get_file() );
	}

	/**
	 * Gets an array of all filenames that are available.
	 *
	 * @return array An array of filenames.
	 **/
	public function get_all_filenames()
	{
		$files = array();
		foreach ( glob( $this->get_path() . "/files/*.xml" ) as $file ) {
			$filename = basename ( $file, ".xml" );
			$files[$filename]= simplexml_load_file( $file );
 		}
		return $files;
	}

	/**
	 * On plugin activation, pick a random quote file.
	 **/

	public function action_plugin_activation( $file )
	{
		if( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			if ( Options::get( self::OPTION_NAME ) == null ) {
				$files = array();
				$files = glob( $this->get_path() . "/files/*.xml" );
				Options::set( self::OPTION_NAME, 
					basename( $files[ rand( 0,count( $files )-1 ) ], ".xml" ) );
			}
		}
	}

	public function theme_randomquote ( $theme )
	{
		$filename = Options::get( self::OPTION_NAME );
		$file = simplexml_load_file ( $this->get_path() . "/files/$filename.xml" );

		$whichone = rand(0,count($file->quote)-1);
		$theme->quote_text = $file->quote[$whichone];
		$theme->quote_author = $file->quote[$whichone]->attributes()->by;
		return $theme->fetch( 'quote' );
	}

	/**
	 * On plugin init, add the template included with this plugin to the available templates in the theme
	 **/
	public function action_init()
	{
		$this->add_template('quote', dirname(__FILE__) . '/quote.php');
	}


}
?>
