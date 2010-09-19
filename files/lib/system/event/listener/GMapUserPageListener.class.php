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

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if($eventName == 'assignVariables') {
			$map_coord = "userOption".User::getUserOptionID('map_coord');
			$map_enable = "userOption".User::getUserOptionID('map_enable');
			$user = $eventObj->frame->getUser();

			if($user->$map_coord != "0,0" && $user->$map_coord != "" && $user->$map_enable == "1") {
				EventHandler::fireAction($this, 'construct'); // overwrite api key?

				WCF::getTPL()->assign(array(
					'user' => $user,
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
