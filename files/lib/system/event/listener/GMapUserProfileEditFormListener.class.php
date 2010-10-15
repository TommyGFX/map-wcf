<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * instantly updates geoposition, using my maps from http://gmaps-samples.googlecode.com/svn/trunk/poly/mymapstoolbar.html
 *
 * @package     de.gmap.wcf
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class GMapUserProfileEditFormListener implements EventListener {
	protected $className, $eventObj;

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$this->eventObj = $eventObj;
		$this->className = $className;
		
		$this->$eventName();
	}
	
	protected function saved() {
		if($this->eventObj->activeCategory == 'profile' && isset($this->eventObj->values['location'])) {

			// update user location			
			if(!empty($this->eventObj->values['location'])) {
				require_once(WCF_DIR.'lib/data/gmap/GmapApi.class.php');
				$api = new GmapApi();
				$point = $api->search($this->eventObj->values['location']);
		
				if($point) {
					$sql = "REPLACE INTO	wcf".WCF_N."_gmap_user
								(userID, pt)
						VALUES		(".intval($this->eventObj->user->userID).",
								PointFromText('POINT(".$point['lon']." ".$point['lat'].")'))";
					WCF::getDB()->sendQuery($sql);
				}
			}

			// drop user location
			else {
				$sql = "DELETE FROM	wcf".WCF_N."_gmap_user
					WHERE 		userID = ".intval($this->eventObj->user->userID);
				WCF::getDB()->sendQuery($sql);
			}
		}
	}
}
?>
