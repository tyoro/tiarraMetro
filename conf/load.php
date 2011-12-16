<?php

include_once '../lib/spyc.php';
include_once '../conf/base.php';

$y2c = new yaml2conf('../conf.yml',$conf,$jsConf);

if( $y2c->check() ){
	print $y2c->errorMessage();
	exit;
}

$conf = $y2c->conf;
$jsConf = $y2c->jsConf;

class  yaml2conf
{
	var $conf;
	var $jsConf;
	var $yaml;
	var $error = false;
	var $errorMessages = array();
	public function __construct( $yaml_file, $conf, $jsConf )
	{
		if( !is_file( $yaml_file ) ){ $this->addError( 'conf.yml not found.' ); }
		$this->yaml = Spyc::YAMLLoad( $yaml_file );
		$this->conf = $conf;
		$this->jsConf = $jsConf;
	}

	
	public function check()
	{	
		//必須
		foreach( Array( 'my_name' ) as $required )
		{
			$this->nullCheck( $required, "$required not found." );
		}

		//setting
		foreach( $this->conf as $key => $def  )
		{
			if(  isset( $this->yaml[$key] ) && !is_array( $this->conf[ $key ] ) ) { $this->conf[ $key ] = $this->yaml[ $key ]; }
		}

		foreach( $this->jsConf as $key => $def )
		{
			if( isset( $this->yaml[ $key ] ) && !is_array( $this->jsConf[ $key ] ) ) { $this->jsConf[ $key ] = $this->yaml[ $key ]; }
		}

		//popup menu
		if( isset($this->yaml[ 'log_popup_menu' ]) )
		{
			if( isset($this->yaml[ 'log_popup_menu' ][ 'separator' ] ) )
			{
				$this->jsConf[ 'log_popup_menu' ][ 'separator' ] = $this->yaml[ 'log_popup_menu' ][ 'separator' ];
			}
			if( isset($this->yaml[ 'log_popup_menu' ][ 'network' ] ) )
			{
				foreach( $this->yaml[ 'log_popup_menu' ][ 'network' ] as $key => $network_setting )
				{
					if( is_string( $network_setting ) ){
						switch( $network_setting )
						{
							case 'fig_default':
								global $fig_default_popup_menu;
								$this->jsConf[ 'log_popup_menu' ][ 'network' ][ $key ] = $fig_default_popup_menu;
								break;
							case 'tig_default':
								global $tig_default_popup_menu;
								$this->jsConf[ 'log_popup_menu' ][ 'network' ][ $key ] = $tig_default_popup_menu;
								break;
							default:
								$this->addError( "network default setting not fodun. [ $network_setting ]");
								continue;
						}
					}
					else
					{
						$this->jsConf[ 'log_popup_menu' ][ 'network' ][ $key ] = $network_setting;
					}
				}
			}
		}
		return $this->error;
	}
	
	public function errorMessage()
	{
		return join("<br/>\n",$this->errorMessages);
	}
	
	//checker
	protected function nullCheck( $check, $message )
	{
		if( is_string( $check) )
		{
			if( isset( $this->yaml[ $check ] )  && !empty( $this->yaml[ $check ] ) )
			{
				return true;
			}
			else
			{
				$this->addError( $message );
				return false;
			}
		}
		else
		{
			if( !empty( $check ) )
			{
				return true;
			}
			else
			{
				$this->addError( $message );
				return false;
			}
		}
	}

	protected function addError( $message )
	{
		array_push( $this->errorMessages, $message );
		$this->error = true;
	}

}

?>
