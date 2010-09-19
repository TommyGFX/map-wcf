<?php
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');
require_once(WCF_DIR.'lib/util/MapDiscover.class.php');

/**
 * Fetchs position data from google maps and stores them in database
 *
 * @package     de.gmap.wcf.data.cronjobs
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class CoordCronjob implements Cronjob {
	/**
	 * clears the cache
	 */
	private function clearCache() {
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.map-*.php');
	}
	
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		$google = new MapDiscover(true);
		
		// fetch column names
                $col = $google->getColumns();

		// if there is no api key specified - abort
		if (defined('MAP_API') && MAP_API != "Api-Key") {
			$csv = array();

			$sql = "SELECT		userID, 
						".$col['street']." AS street,
						".$col['zip']." AS zip,
						".$col['city']." AS city,
						".$col['country']." AS country
				FROM		wcf".WCF_N."_user_option_value
				WHERE		(".$col['enable']." = 1 AND ".$col['coord']." = '' AND ".$col['location']." != '') 
				OR		CONCAT(".$col['street'].",".$col['zip'].",".$col['city'].",".$col['country'].") != ".$col['lastlookup']." 
				GROUP BY	CONCAT(".$col['street'].",".$col['zip'].",".$col['city'].",".$col['country'].")
				LIMIT 		100; ";
			$result = WCF::getDB()->sendQuery($sql);

			while ($row = WCF::getDB()->fetchArray($result)) {
				$google->update($row['street'], $row['zip'], $row['city'], $row['country']);
			}
		}
	}
}
?>
