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
		if($this->eventObj->activeCategory == 'profile') {
			require_once(WCF_DIR.'lib/data/gmap/GmapApi.class.php');
			$api = new GmapApi();

			// build search query
			$search = array();
			foreach($api->getFields() as $key) {
				if(isset($this->eventObj->values[$key])) {
					$search[] = $this->eventObj->values[$key];
				}
			}
			
			// wrong form
			if(count($search) == 0) {
				return;
			}
			
			$query = trim(implode(' ', $search));
			if($query) {
				$point = $api->search($query);
				if(!$point) {
					WCF::getTPL()->append('userMessages', '<p class="error">'.WCF::getLanguage()->get('wcf.map.noPosition').'</p>');
				}
			} else {
				$point = false;
			}
	
			if($point) {
				$sql = "REPLACE INTO	wcf".WCF_N."_gmap_user
							(userID, pt)
					VALUES		(".intval($this->eventObj->user->userID).",
							PointFromText('POINT(".$point['lon']." ".$point['lat'].")'))";
				WCF::getDB()->sendQuery($sql);
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
