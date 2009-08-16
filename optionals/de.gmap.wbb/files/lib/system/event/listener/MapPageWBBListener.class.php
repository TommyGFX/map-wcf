<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * optimizes wcf package for wbb support
 *
 * @package     de.gmap.wbb.system.event.listener
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapPageWBBListener implements EventListener {
	private $spammers = 'false';

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		switch($eventName) {
			case 'readParameters':
				if(isset($_GET['spammers'])) {
					$eventObj->ajax_filter['spammers'] = $this->spammers = 'true';
				}
			break;
			
			case 'assignVariables':
				if(MAP_BIGMAP_GROUPFILTER != "n" && MAP_GROUP_SPAMMERS) {
					$lang = addslashes(WCF::getLanguage()->get('wcf.map.filter_spammers'));
					WCF::getTPL()->append('additionalFilters', 
						"ajax_filter.spammers = {$this->spammers};
						gAddGroup(\"gFilterOption('spammers', this.checked)\", '{$lang}', ajax_filter.spammers);");
				}
			break;
		
		}
	}
}
?>
