<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * optimizes wcf package for wbb support
 *
 * @package     de.gmap.wbb.system.event.listener
 * @author      Michael Senkler, Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapAjaxPageWBBListener implements EventListener {

	/**
	 * read Spammers
	 * @return userIDs (array)
	 */
	public function readSpammers() {
		// get user ids for active page
		$userIDs = array();
		$sql = "SELECT		userID
			FROM		wcf".WCF_N."_user wcf
			NATURAL JOIN	wbb".WBB_N."_user wbb
			ORDER BY	wbb.posts DESC
			LIMIT		".MEMBERS_LIST_USERS_PER_PAGE;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$userIDs[] = $row['userID'];
		}
		return $userIDs;
	}

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if($eventName == 'readParameters') {
			// extension: spammers
			if(isset($_GET['spammers'])) {
				$eventObj->appendFilter('users', $this->readSpammers());
			}
			
			
			// extension: use wbb for more active users
			$type = 'cities';
			$columns = array();
			$joins = array("NATURAL JOIN wbb".WBB_N."_user wbbuser");
			$conditions= array();
			$groups = array();
			$orders = array('wbbuser.posts DESC');
			$eventObj->addExtension($type, $columns, $joins, $conditions, $groups, $orders);
		}
	}
}
?>
