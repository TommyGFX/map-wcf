<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * adds another item to the maintenance list
 * 
 * @package     de.gmap.wbb.acp.action
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapUpdateCountersFormListener implements EventListener {

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$eventObj->counters['map'] = 25;
	}
}
?>
