<?php

// This is the uninstall script.

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
	exit();
    
		unregister_setting('discussion','ippy_bcq_options');