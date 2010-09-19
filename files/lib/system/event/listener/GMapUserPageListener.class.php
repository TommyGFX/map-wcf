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
		
		
		return; // TODO: disabled
	
		$this->$eventName();
	}
	
	protected function readData() {
		
	}
	
	protected function assignVariables() {

		WCF::getTPL()->assign(array(
			'user' => $user,
			'gmap_map_key' => $this->map_key
		));

		// if user position exists
		WCF::getTPL()->append('additionalBoxes2', WCF::getTPL()->fetch('userProfileMapSide'));

		// if user is owner or user has personal maps
		WCF::getTPL()->append('additionalContents3', WCF::getTPL()->fetch('userProfileMapCenter'));
	}
}
?>
