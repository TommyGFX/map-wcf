<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/acp/option/Options.class.php');

/**
 * Overwrite the api key
 *
 * @package     de.gmap.wcf.system.event.listener
 * @author      Michael Senkler, Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapKeyListener implements EventListener {

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if(defined('PAGE_URLS') && PAGE_URLS != '') {
			$domain2key = unserialize(MAP_API_PAGE_URLS);
			foreach($domain2key as $domain => $key) {
				$domain =  str_replace(array("http://","https://"), "", $domain);
				if($_SERVER['HTTP_HOST'] == $domain) {
					$eventObj->map_key = $key;
				}
			}
		}
	}
}
?>
