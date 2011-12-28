<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * extends the userpage with the map - maybe also add the personal points
 *
 * @package     de.gmap.wcf.system.event.listener
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class GMapUserPageListener implements EventListener {
	protected $coordinate = null;
	protected $userID = 0;

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {

		// skip
		if(!MODULE_GMAP || !WCF::getUser()->getPermission('user.profile.gmap.canViewUsers')) {
			return;
		}

		$this->eventObj = $eventObj;
		$this->className = $className;

		$this->$eventName();
	}

	protected function readData() {
		$this->userID = $this->eventObj->frame->getUserID();
		
		// read user location
		$sql = 'SELECT		X(pt) AS lon,
					Y(pt) AS lat
			FROM		wcf'.WCF_N.'_gmap_user
			WHERE		userID = '.intval($this->userID);
		$result = WCF::getDB()->sendQuery($sql);
		$this->coordinate = WCF::getDB()->fetchArray($result);
	}
	
	protected function assignVariables() {
		if($this->coordinate) {
			WCF::getTPL()->assign(array(
				'coordinate' => $this->coordinate
			));
			// if user position exists
			WCF::getTPL()->append('additionalBoxes1', WCF::getTPL()->fetch('userProfileMapSide'));
		}
	}
}
?>
