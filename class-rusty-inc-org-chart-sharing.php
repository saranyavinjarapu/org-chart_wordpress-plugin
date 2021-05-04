<?php

class Rusty_Inc_Org_Chart_Sharing {
	const OPTION_NAME = 'rusty-inc-org-chart-key';

	public function regenerate_key() {
		update_option( self::OPTION_NAME, wp_generate_password( 8 ) );
	}

	public function key() {
		return get_option( self::OPTION_NAME, 'baba' );
	}

	public function url() {
		$key = $this->key();
		return $key ? network_home_url( '/?tree=' ) . $this->key() : null;
	}

	public function does_url_have_valid_key() {
		return isset( $_GET['tree'] ) && ( empty( $_GET['tree'] ) || $_GET['tree'] === $this->key() );
	}
}
