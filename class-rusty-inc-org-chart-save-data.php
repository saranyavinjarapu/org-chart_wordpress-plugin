<?php
require_once '../../../wp-load.php';
require_once __DIR__ . '/class-rusty-inc-org-chart-sharing.php';
require_once __DIR__ . '/class-rusty-inc-org-chart-plugin.php';
require_once __DIR__ . '/class-rusty-inc-org-chart-tree.php';


if ($_SERVER["REQUEST_METHOD"] == "POST")
	{

		$teamAddData = isset($_POST['addTeamData']) ? $_POST['addTeamData'] : [];
		$teamDeleteData = isset($_POST['deleteTeamData']) ? $_POST['deleteTeamData'] : [];

		//if the teams to be saved array passed is not empty
		if (!empty($teamAddData) || !empty($teamDeleteData))
		{
			$teamData = array($teamAddData,$teamDeleteData);

			if (class_exists('Rusty_Inc_Org_Chart_Save_Data'))
			{
				$chart_save = new Rusty_Inc_Org_Chart_Save_Data($teamData);
				$chart_save->save_teams();

			}
		}

	}


class Rusty_Inc_Org_Chart_Save_Data
{
    private $teams_to_save;
        
    /**
     * @param array list of teams that are added to the tree and need to be saved
     */
    public function __construct($teamData)
    {
        $this->teams_to_save = $teamData;
        $this->teams_to_add = $this->teams_to_save[0];
        $this->teams_to_delete = $this->teams_to_save[1];
        $this->new_generated_org_chart = '';

    }

    //save teams that are newly added
    public function save_teams()
    {
        $deleteSuccess = 0;
        $insertSuccess = 0;
        
        if (!empty($this->teams_to_delete) && isset($this->teams_to_delete))
        {
            //call delete_teams_from_db() function to delete teams  from  database
            $deleteSuccess = $this->delete_teams_from_db();
        }
        if (!empty($this->teams_to_add) && isset($this->teams_to_add))
        {
            //call insert_teams_into_db() function to insert teams into database
            $insertSuccess = $this->insert_teams_into_db();
        }
      
         //check if all teams of the passed array have been inserted/deleted successfully
        if ($deleteSuccess == count($this->teams_to_delete) || count($this->teams_to_add) == $insertSuccess)
        {
            //call regenerate_new_url function to regenerate new key and url
            $new_generated_url = $this->regenerate_new_url();

            //call regenerate_new_org_chart function to regenerate org chart for the new tree with saved data
            $this->new_generated_org_chart = $this->regenerate_new_org_chart();

            //call regenerate_new_tree function to regenerate tree with newly saved teams
            $new_generated_tree = $this->regenerate_new_tree();

            //return newly generated tree and url as array provided both values are not empty
            if ($new_generated_url && !empty($new_generated_tree))
            {
                $saveDataReturnArray = array(
                    "newTree" => $new_generated_tree,
                    "newUrl" => $new_generated_url
                );
                echo json_encode($saveDataReturnArray);
            }
            //return 404 failure error
            else
            {
                echo 404;
            }

        }

    }

    public function delete_teams_from_db()
    {
        $deleteTeamData = $this->teams_to_delete;
        $delete_result_check_count = 0;
        global $wpdb;

        //loop through delete teams data array
        for ($i = 0;$i < count($deleteTeamData);$i++)
        {
            $delete_id = $deleteTeamData[$i];

            $delete_result_check_count = $wpdb->delete('wp_org_chart_plugin', array(
                'id' => $delete_id
            ));
        }

        return $delete_result_check_count;

    }
    //function to insert teams into database
    public function insert_teams_into_db()
    {

        $newTeamData = $this->teams_to_add;
        $insert_result_check_count = 0;
        global $wpdb;

        //loop through added teams data array
        for ($i = 0;$i < count($newTeamData);$i++)
        {
            //insert added teams into database and get the count of successful inserts into database
            $result_check = $wpdb->query($wpdb->prepare("INSERT INTO wp_org_chart_plugin(name,emoji,parent_id) values('%s','%s',%d) ", $newTeamData[$i]['name'], $newTeamData[$i]['emoji'], $newTeamData[$i]['parent_id']));

            //check if all the added teams to be saved are inserted into database or not
            if (!empty($result_check))
            {
                $insert_result_check_count++;
            }

        }

        return $insert_result_check_count;

    }

    //function to regenerate new key and url
    public function regenerate_new_url()
    {

        if (class_exists('Rusty_Inc_Org_Chart_Sharing'))
        {
            $chartSharing = new Rusty_Inc_Org_Chart_Sharing();

            //generate new key and url value
            $chartSharing->regenerate_key();
            $urlValue = $chartSharing->url();
            return $urlValue;

        }

    }

    //function to regenerate org chart for the new tree with saved data
    public function regenerate_new_org_chart()
    {
        if (class_exists('Rusty_Inc_Org_Chart_Plugin'))
        {
            $chartPlugin = new Rusty_Inc_Org_Chart_Plugin();

            //generate default organization chart array for updated tree
            $newOrgChartArray = $chartPlugin->get_org_chart_array();

            return $newOrgChartArray;
        }

    }

    //function to regenerate tree with newly saved teams
    public function regenerate_new_tree()
    {

        if (class_exists('Rusty_Inc_Org_Chart_Tree'))
        {
            $chartNewTree = new Rusty_Inc_Org_Chart_Tree($this->new_generated_org_chart);

            $newTreeJs = $chartNewTree->get_nested_tree_js();

            return $newTreeJs;
        }

    }

}

