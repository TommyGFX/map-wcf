<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Displays a map in the acp to set center and zoom
 *
 * @package     de.gmap.wcf.system.event.listener
 * @author      Michael Senkler, Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class GMapOptionFormListener implements EventListener {

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventObj->activeCategory == 'map' && $eventName == 'assignVariables') {
			WCF::getTPL()->append(
				'additionalFields', WCF::getTPL()->fetch('mapAdmin')
			);
		}
	}
}
?>
