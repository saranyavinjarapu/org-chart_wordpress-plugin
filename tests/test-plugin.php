<?php
require_once __DIR__ . '/../class-rusty-inc-org-chart-plugin.php';

class Rusty_Inc_Org_Chart_Plugin_Test extends WP_UnitTestCase {
	public function test_ui_is_given_default_tree_when_option_is_missing() {
		$plugin = new Rusty_Inc_Org_Chart_Plugin();
		$plugin->scripts_in_footer();
		$tree_json_prefix = '{"id":1,"name":"Rusty Corp.","emoji":"\ud83d\udc15","parent_id":null,"children":[';
		$this->expectOutputContains( $tree_json_prefix );
	}

	public function test_ui_uses_tree_from_option() {
		$plugin = new Rusty_Inc_Org_Chart_Plugin();
		// WordPress automatically resets all database changes we make, so we don't need to undo them
		update_option( $plugin::OPTION_NAME, [ [ 'id' => 1, 'name' => 'Baba', 'emoji' => '👪', 'parent_id' => null ] ] );
		$plugin->scripts_in_footer();
		$tree_json_prefix = '{"id":1,"name":"Baba","emoji":"\ud83d\udc6a","parent_id":null,"children":[';
		$this->expectOutputContains( $tree_json_prefix );
	}

//	public function test_

	private function expectOutputContains( $substring ) {
		$this->expectOutputRegex( '/' . preg_quote( $substring ) . '/' );
	}
}
