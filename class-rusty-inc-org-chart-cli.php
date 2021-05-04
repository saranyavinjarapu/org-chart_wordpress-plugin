<?php
require_once __DIR__ . '/class-rusty-inc-org-chart-plugin.php';

class Rusty_Inc_Org_Chart_CLI {
	/**
	 * Sets the org chart in the plugin to a pre-defined value.
	 *
	 * WARNING! This will permanently erase the current org-chart!
	 *
	 * ## OPTIONS
	 * --type=<sample|big|custom>
	 * : What type of org-chart to install (sample, big, custom)
	 * • sample: the small org-chart that is installed by default
	 * • big: automatically generated org-chart with 3 sub-teams of each team and 9 levels great for performance tests
	 * • custom: configure number of sub-teams and levels with the options below
	 *
	 * [--levels]
	 * : How many levels deep should the custom org-chart go
	 * [--sub-teams]
	 * : How many sub-teams should each team have in a custom org-chart
	 */
	function set( $args, $assoc_args ) {
		switch( $assoc_args['type'] ) {
			case 'sample':
				$tree = $this->get_sample( $assoc_args );
				break;
			case 'big':
				$tree = $this->get_big( $assoc_args );
				break;
			case 'custom':
				$tree = $this->get_custom( $assoc_args );
				break;
			default:
				WP_CLI::error( 'Invalid org chart type "' . $assoc_args['type'] . '". Try sample, custom, or big.' );
		}
		if ( get_option( Rusty_Inc_Org_Chart_Plugin::OPTION_NAME ) ) {
			WP_CLI::log( 'Deleting the current org chart first…' );
			$deleted = delete_option( Rusty_Inc_Org_Chart_Plugin::OPTION_NAME );
			if ( ! $deleted ) {
				WP_CLI::error( 'Error setting the org chart, delete_option failed' );
			}
		}
		WP_CLI::log( 'Setting the new org chart…' );
		$updated = update_option( Rusty_Inc_Org_Chart_Plugin::OPTION_NAME, $tree );
		if ( ! $updated ) {
			WP_CLI::error( 'Error setting the org chart, update_option failed' );
		}
		WP_CLI::success( 'Done!' );
	}

	private function get_sample( $assoc_args ) {
		return Rusty_Inc_Org_Chart_Plugin::DEFAULT_ORG_CHART;
	}

	private function get_big( $assoc_args ) {
		return $this->get_custom( [ 'levels' => 9, 'sub-teams' => 3 ] );
	}

	private function get_custom( $assoc_args ) {
		if ( ! isset ( $assoc_args['levels'] ) || ! isset( $assoc_args[ 'sub-teams' ] ) ) {
			WP_CLI::error( 'Both --levels and --sub-teams must be specified for a custom org chart' );
		}
		return $this->generate_org_chart( $assoc_args['levels'], $assoc_args['sub-teams'] );
	}

	private function generate_org_chart( $levels, $sub_teams ) {
		$flat_tree = [ 1 => [ 'id' => 1, 'parent_id' => null, 'emoji' => '🍓', 'name' => 'Land of Nice and Competent People' ] ];
		$next_id = 2;
		$previous_level = [ 1 ];
		for( $level = 0; $level < $levels - 1; $level++ ) {
			$this_level = [];
			foreach( $previous_level as $parent_id ) {
				for ( $sub_team = 0; $sub_team < $sub_teams; $sub_team++ ) {
					$id = $next_id++;
					$flat_tree[$id] = [ 'id' => $id, 'parent_id' => $parent_id, 'emoji' => '👋', 'name' => "Respected Group 🍉 ($id)" ];
					$this_level[] = $id;
				}
			}
			$previous_level = $this_level;
		}
		return $flat_tree;
	}
}

