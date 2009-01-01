<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * updates geoposition
 *
 * @package     de.gmap.wcf
 * @author      Michael Senkler, Torben Brodt
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
		
		if($this->eventObj->activeCategory != 'profile') return;

		switch($eventName) {
			case 'saved':
				$this->saved();
			break;
		}
	}
	
	/**
	 * @see Form::saved()
	 */
	protected function saved() {
		if(isset($this->eventObj->values['map_enable'])) {

			$this->eventObj->values['map_street'] = isset($this->eventObj->values['map_street']) ? $this->eventObj->values['map_street'] : "";
			$this->eventObj->values['map_zip'] = isset($this->eventObj->values['map_zip']) ? $this->eventObj->values['map_zip'] : "";
			$this->eventObj->values['map_city'] = isset($this->eventObj->values['map_city']) ? $this->eventObj->values['map_city'] : "";
			$this->eventObj->values['map_country'] = isset($this->eventObj->values['map_country']) ? $this->eventObj->values['map_country'] : "";

			// update geoposition
			$google = new MapDiscover();
			$google->update($this->eventObj->values['map_street'], 
					$this->eventObj->values['map_zip'], 
					$this->eventObj->values['map_city'], 
					$this->eventObj->values['map_country']
				);
		} else {
			$map_coord = "userOption".User::getUserOptionID('map_coord');
			$userID = $this->eventObj->userID == 0 ? WCF::getUser()->userID : $this->eventObj->userID;
			$sql = "UPDATE		wcf".WCF_N."_user_option_value 
				SET 
						{$map_coord} = ''
				WHERE 		userID = {$userID}; ";
			WCF::getDB()->sendQuery($sql);
		}
	}
}
?>
