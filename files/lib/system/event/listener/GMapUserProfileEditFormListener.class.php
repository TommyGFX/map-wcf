<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * instantly updates geoposition
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
		
		if($this->eventObj->activeCategory == 'profile' && $eventName == 'saved') {
			if(isset($this->eventObj->values['location']) && !empty($this->eventObj->values['location'])) {
				MapDiscover::update($this->eventObj->values['location']);
			}
		}
	}
}
?>
