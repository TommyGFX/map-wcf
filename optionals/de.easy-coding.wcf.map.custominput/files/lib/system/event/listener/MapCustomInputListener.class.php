<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Admin to configure custom useroptions.
 * 
 * @author	Torben Brodt
 * @copyright	2010 easy-coding.de
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 * @package	de.easy-coding.wcf.map.custominput
 */
class MapCustomInputListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		
		if($eventObj->activeCategory != 'general') {
			return;
		}
	
		switch($eventName) {
			case 'readData':
				$this->current = explode(",", $eventObj->activeOptions['gmap_custominput']['optionValue']);
				$this->getOptionIDs();
				$this->readOptions();
			break;
			case 'assignVariables':
				WCF::getTPL()->assign(array(
					'customInputOptions' => $this->options
				));
			
				WCF::getTPL()->append('additionalHeaderButtons', '<script type="text/javascript">
				function changeCustomInput(id) {
					var d = document.getElementById("gmap_custominput");
					var s = [];
					var l = d.value == "" ? [] : d.value.split(",");
					var existing = false;
					for(var i=0; i<l.length; i++) {
						if(l[i] == id) {
							document.getElementById("status_" + id).src = RELATIVE_WCF_DIR + "icon/disabledS.png";
							existing = true;
							continue;
						}
						s.push(l[i]);
					}
					if(!existing) {
						document.getElementById("status_" + id).src = RELATIVE_WCF_DIR + "icon/enabledS.png";
						s.push(id);
					}
					d.value = s.join(",");
					
					return false;
				}
				onloadEvents.push(function() {
					var d = document.createElement("div");
					d.innerHTML = '.json_encode(WCF::getTPL()->fetch('mapCustomInput')).';
					document.getElementById("gmap_custominputDiv").appendChild(d);
					
					document.getElementById("gmap_custominput").style.display = "none";
				});
				</script>');
			break;
		}
	}
	
	/**
	 * Gets user options ids.
	 */
	protected function getOptionIDs() {
		$sql = "SELECT		optionName, optionID 
			FROM		wcf".WCF_N."_user_option option_table,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		option_table.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".PACKAGE_ID."
					AND option_table.categoryName IN (
						SELECT	categoryName
						FROM	wcf".WCF_N."_user_option_category
						WHERE	parentCategoryName = 'profile'
					)
					AND option_table.editable < 4
					AND option_table.visible < 4
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$options = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$options[$row['optionName']] = $row['optionID'];
		}
		
		$this->optionIDs = implode(',', $options);
	}
	
	/**
	 * Gets a list of user options.
	 */
	protected function readOptions() {
		$this->options = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_user_option
			WHERE		optionID IN (".$this->optionIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['isCustomInput'] = in_array($row['optionName'], $this->current);
			$this->options[] = $row;
		}
	}
}
?>
