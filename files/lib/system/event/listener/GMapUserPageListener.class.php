<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * extends the userpage with the map
 *
 * @package     de.gmap.wcf.system.event.listener
 * @author      Michael Senkler, Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class GMapUserPageListener implements EventListener {
	// map key default setting
	public $map_key = MAP_API;

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if(!WCF::getUser()->getPermission('user.map.canView')) // check permission
			return;

		if($eventName == 'assignVariables') {
			$map_coord = "userOption".User::getUserOptionID('map_coord');
			$map_enable = "userOption".User::getUserOptionID('map_enable');			
			
			if($eventObj->user->$map_coord != "0,0" && $eventObj->user->$map_coord != "" && $eventObj->user->$map_enable == "1") {
				EventHandler::fireAction($this, 'construct'); // overwrite api key?

				WCF::getTPL()->assign(array(
					'user' => $eventObj->user,
					'gmap_map_key' => $this->map_key
				));

				if(MAP_USERMAP_SHOW_RIGHT) {
					WCF::getTPL()->append('additionalBoxes2', WCF::getTPL()->fetch('userProfileMapSide'));
				}
				if(MAP_USERMAP_SHOW_CENTER) {
					WCF::getTPL()->append('additionalContents3', WCF::getTPL()->fetch('userProfileMapCenter'));
				}
			}
		}
	}
}
?>
