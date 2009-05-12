<?php
require_once(WBB_DIR.'lib/acp/action/UpdateCounterAction.class.php');
require_once(WCF_DIR.'lib/util/MapDiscover.class.php');

/**
 * Updates the map locations
 * 
 * @package     de.gmap.wbb
 * @author      Michael Senkler, Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class UpdateMapAction extends UpdateCounterAction {
	public $action = 'UpdateMap';
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		$google = new MapDiscover(true);
		
		// fetch column names
                $col = $google->getColumns();
                
                
		//Setting default Values. (Location to City)
		$sql = "UPDATE		wcf".WCF_N."_user_option_value 
			SET 
					".$col['city']." = ".$col['location']."
			WHERE 		".$col['enable']." = 1 
			AND		".$col['location']." != '' 
			AND 		".$col['city']." = '' 
			AND		".$col['lastlookup']." = ''; ";
		WCF::getDB()->sendQuery($sql);

		// if there is no api key specified - abort
		if (defined('MAP_API') && MAP_API != "Api-Key") {
			$this->calcProgress();
			$this->finish();
		}
                
		// count
		$sql = "SELECT 	COUNT(userID) AS c
			FROM (
				SELECT          userID
				FROM            wcf".WCF_N."_user_option_value
				WHERE           (".$col['enable']." = 1 AND ".$col['location']." != '')
				GROUP BY        CONCAT(".$col['street'].",".$col['zip'].",".$col['city'].",".$col['country'].")
			) x";
		$row = WCF::getDB()->getFirstRow($sql);
		$count = $row['c'];

		
		$sql = "SELECT		userID, 
					".$col['street']." AS street,
					".$col['zip']." AS zip,
					".$col['city']." AS city,
					".$col['country']." AS country
			FROM		wcf".WCF_N."_user_option_value
			WHERE		(".$col['enable']." = 1 AND ".$col['location']." != '') 
			GROUP BY	CONCAT(".$col['street'].",".$col['zip'].",".$col['city'].",".$col['country'].")";
		$result = WCF::getDB()->sendQuery($sql, $this->limit, ($this->limit * $this->loop));
		if (!WCF::getDB()->countRows($result)) {
			$this->calcProgress();
			$this->finish();
		}
		while ($row = WCF::getDB()->fetchArray($result)) {
			$google->update($row['street'], $row['zip'], $row['city'], $row['country']);
		}
		
		$this->executed();

		$this->calcProgress(($this->limit * $this->loop), $count);
		$this->nextLoop();
	}
}
?>
