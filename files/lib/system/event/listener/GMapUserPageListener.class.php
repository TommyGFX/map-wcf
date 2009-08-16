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
		if(!WCF::getUser()->getPermission('user.map.canView')) // check permission
			return;

		if($eventName == 'assignVariables') {
			WCF::getTPL()->assign(array(
				'user' => $eventObj->frame->getUser(),
				'gmap_map_key' => $this->map_key
			));

			if(MAP_USERMAP_SHOW_RIGHT) {
				WCF::getTPL()->append('additionalBoxes2', WCF::getTPL()->fetch('userProfileMapSide'));
			}
			if(MAP_USERMAP_SHOW_CENTER) {
				WCF::getTPL()->append('additionalContent3', WCF::getTPL()->fetch('userProfileMapCenter'));
			}
		}
	}
}
?>
