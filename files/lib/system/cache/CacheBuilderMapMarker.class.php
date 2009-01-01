<?php
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches mapMarker images
 *
 * @package     de.gmap.wcf.data.cronjobs
 * @author      Michael Senkler, Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class CacheBuilderMapMarker implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$cache = WCF::getCache()->get('g-map');
		$data = $cache;
	        return $data;
	}
}
?>
