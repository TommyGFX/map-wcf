<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/acp/option/Options.class.php');

/**
 * Allow every page urls its own api key
 *
 * @package     de.gmap.wcf.system.event.listener
 * @author      Michael Senkler, Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class GMapWBBOptionFormListener implements EventListener {
	private $pageurls = array();

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventObj->activeCategory != 'map') return;

		switch($eventName) {
		case 'assignVariables':
			if(defined('PAGE_URLS') && PAGE_URLS != '') {
				if(empty($this->pageurls)) {
					$domains = explode("\n", StringUtil::unifyNewlines(PAGE_URLS));
					$domain2key = unserialize(MAP_API_PAGE_URLS);
					
					$this->pageurls = array();
					foreach($domains as $domain) {
						if(array_key_exists($domain, $domain2key)) {
							$this->pageurls[$domain] = $domain2key[$domain];
						} else {
							$this->pageurls[$domain] = '';
						}
					}
				}

				WCF::getTPL()->assign('page_urls', $this->pageurls);
			}
			WCF::getTPL()->append(
				'additionalFields', WCF::getTPL()->fetch('mapAdminPageUrls')
			);
		break;
		case 'readFormParameters':
			if(isset($_POST['values']['pageurl'])) {
				$this->pageurls = $_POST['values']['pageurl'];
			}
		break;
		case 'saved':
			if(!empty($this->pageurls)) {
				$sql = "SELECT		optionName, optionID 
					FROM		wcf".WCF_N."_option acp_option,
							wcf".WCF_N."_package_dependency package_dependency
					WHERE 		acp_option.packageID = package_dependency.dependency
							AND package_dependency.packageID = ".PACKAGE_ID."
					ORDER BY	package_dependency.priority";
				$result = WCF::getDB()->sendQuery($sql);
				$optionIDs = array();
				while ($row = WCF::getDB()->fetchArray($result)) {
					$key = strtoupper($row['optionName']);
					$optionIDs[$key] = $row['optionID'];
				}
				
				$id = $optionIDs['MAP_API_PAGE_URLS'];
			
				// save options
				$saveOptions = array();
				$saveOptions[$id] = serialize($this->pageurls);
				Options::save($saveOptions);

				// clear cache
				Options::resetCache();
				
				WCF::getTPL()->assign('page_urls', $this->pageurls);
			}
		break;
		}
	}
}
?>
