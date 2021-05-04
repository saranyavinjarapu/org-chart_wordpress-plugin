<?php
/*
 * Plugin Name: Rusty Inc. Org Chart
 * Plugin URI: https://automattic.com/work-with-us/
 * Description: Simple UI to help making sense of a leading canine organization
 * Version: 0.1
 * Author: Engineering Hiring @ Automattic
 */

require_once __DIR__ . '/class-rusty-inc-org-chart-plugin.php';

if (!defined('ABSPATH')) { die; }

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__  . '/class-rusty-inc-org-chart-cli.php';
	WP_CLI::add_command( 'rusty', 'Rusty_Inc_Org_Chart_CLI' );
}

if(class_exists('Rusty_Inc_Org_Chart_Plugin'))
{
( new Rusty_Inc_Org_Chart_Plugin() )->add_init_action();
}


  
	 

