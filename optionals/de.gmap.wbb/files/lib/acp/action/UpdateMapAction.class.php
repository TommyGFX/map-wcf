<?php
require_once(WBB_DIR.'lib/acp/action/UpdateCounterAction.class.php');
require_once(WCF_DIR.'lib/util/MapDiscover.class.php');

/**
 * Updates the map locations
 * 
 * @package     de.gmap.wbb
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class UpdateMapAction extends UpdateCounterAction {
	public $action = 'UpdateMap';
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// if there is no api key specified - abort
		if (!defined('MAP_API') || MAP_API != "") {
			$this->calcProgress();
			$this->finish();
		}
                
		// how many loops should be done
		$sql = "SELECT 	COUNT(userID) AS c
			FROM (
				SELECT          userID
				FROM            wcf".WCF_N."_user
				GROUP BY	location
			) x";
		$row = WCF::getDB()->getFirstRow($sql);
		$count = $row['c'];

		// start with biggest location
		$sql = "SELECT		COUNT(userID) AS c,
					".$col['location']." AS location
			FROM		wcf".WCF_N."_user_option_value
			GROUP BY	location
			ORDER BY	c DESC";
		$result = WCF::getDB()->sendQuery($sql, $this->limit, ($this->limit * $this->loop));
		if (!WCF::getDB()->countRows($result)) {
			$this->calcProgress();
			$this->finish();
		}
		while ($row = WCF::getDB()->fetchArray($result)) {
			MapDiscover::update($row['location']);
		}
		
		$this->executed();

		$this->calcProgress(($this->limit * $this->loop), $count);
		$this->nextLoop();
	}
}
?>
