<?php
// wcf importsrequire_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * Cache Builder for Map Box
 *
 * @package     de.gmap.wcf.system.cache
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class CacheBuilderMapBox implements CacheBuilder {
	/**
	* @see CacheBuilder::getData()
	*/
	public function getData($cacheResource) {
		$data = array();		
		$col = User::getUserOptionID('gmap');

		$sql = "SELECT		useroptions.userID, 
					useroptions.useroption{$col} as birthday, 
					user.userID,
					user.username
			FROM 		wcf" . WCF_N . "_user_option_value useroptions
			LEFT JOIN 	wcf" . WCF_N . "_user user
			ON 		(useroptions.userID = user.userID); ";

		$result = WBBCore::getDB()->sendQuery($sql);
		while ($row = WBBCore::getDB()->fetchArray($result)) {
			$data[] = $row;
		}
		return $data;
	}
}
?>
