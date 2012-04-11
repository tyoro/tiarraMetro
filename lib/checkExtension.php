<?php

function checkExtension()
{
	$arg_arr = func_get_args();
	foreach( $arg_arr as $arg ){
		if( !extension_loaded( $arg ) )
		{
			exit( $arg . " php module is not found" );
		}
	}
}

