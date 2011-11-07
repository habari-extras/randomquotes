<?php

/**
 * Random Quotes plugin for Habari 0.7+
 * 
 * @package randomquotes
 */

class RandomQuotes extends Plugin
{
	const OPTION_NAME = 'randomquotes__filename';

	/**
	 * Outputs the options form on the plugin page.
	 **/
	public function configure()
	{
		$form = new FormUI( 'randomquotes' );
		$control = $form->append('select', 'control', self::OPTION_NAME, _t( 'Quotations file', 'randomquotes' ) );
		foreach( $this->get_all_filenames() as $filename => $file ) {
			$control->options[$filename] = $file->info->name . ": " . $file->info->description;
		}
		$control->add_validator( 'validate_required' );
		$form->append( 'submit', 'save', _t( 'Save', 'randomquotes' ) );
		return $form;
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
		if ( Options::get( self::OPTION_NAME ) == null ) {
			$files = array();
			$files = glob( $this->get_path() . "/files/*.xml" );
			Options::set( self::OPTION_NAME,
				basename( $files[ rand( 0,count( $files )-1 ) ], ".xml" ) );
		}
	}

	public function theme_randomquote ( $theme )
	{
		$filename = Options::get( self::OPTION_NAME );
		$file = simplexml_load_file ( $this->get_path() . "/files/$filename.xml" );
		$whichone = rand(0,count($file->quote)-1);
		$theme->quote_text = (string) $file->quote[$whichone];
		$theme->quote_author = (string) $file->quote[$whichone]->attributes()->by;
		return $theme->fetch( 'quote' );
	}

	/**
	 * On plugin init, add the template included with this plugin to the available templates in the theme
	 **/
	public function action_init()
	{
		$this->load_text_domain( 'randomquotes' );
		$this->add_template( 'quote', dirname(__FILE__) . '/quote.php' );
		$this->add_template( 'block.randomquote', dirname(__FILE__) . '/block.randomquote.php' );
	}


	/**
	 * Add random quote block to the list of selectable blocks
	 **/
	public function filter_block_list( $block_list )
	{
		$block_list[ 'randomquote' ] = _t( 'Random Quote', 'randomquotes' );
		return $block_list;
	}


	/**
	 * Output the content of the block
	 **/
	public function action_block_content_randomquote( $block, $theme )
	{
		$filename = $block->filename;
		$file = simplexml_load_file ( $this->get_path() . "/files/$filename.xml" );
		$whichone = rand(0,count($file->quote)-1);
		$block->quote_text = (string) $file->quote[$whichone];
		$block->quote_author = (string) $file->quote[$whichone]->attributes()->by;
		return $block;
	}

	/**
	 * Block options
	 **/
	public function action_block_form_randomquote( $form, $block )
	{
		$control = $form->append( 'select', 'filename', $block, _t( 'Quotations file', 'randomquotes' ) );
		foreach( $this->get_all_filenames() as $filename => $file ) {
			$control->options[$filename] = $file->info->name . ": " . $file->info->description;
		}
		$control->add_validator( 'validate_required' );
	}

}
?>
