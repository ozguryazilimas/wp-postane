<?php

// This is the uninstall script.

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
	exit();
    
		register_setting('discussion','ippy_bcq_options');