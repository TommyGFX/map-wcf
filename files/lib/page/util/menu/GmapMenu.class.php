<?php
// wcf imports
require_once(WCF_DIR.'lib/page/util/menu/TreeMenu.class.php');

/**
 * Builds the gmap menu.
 * 
 * @author	Torben Brodt
 * @copyright	2010 easy-coding.de
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 * @package	de.gmap.wcf.data.page.map
 */
class GmapMenu extends TreeMenu {
	protected static $instance = null;
	
	/**
	 * Returns an instance of the GmapMenu class.
	 * 
	 * @return	GmapMenu
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * @see TreeMenu::loadCache()
	 */
	protected function loadCache() {
		parent::loadCache();
		
		WCF::getCache()->addResource('gmapMenu', WCF_DIR.'cache/cache.gmapMenu.php', WCF_DIR.'lib/system/cache/CacheBuilderGmapMenu.class.php');
		$this->menuItems = WCF::getCache()->get('gmapMenu');
	}
	
	/**
	 * @see TreeMenu::parseMenuItemLink()
	 */
	protected function parseMenuItemLink($link, $path) {
		if (preg_match('~\.php$~', $link)) {
			$link .= SID_ARG_1ST; 
		}
		else {
			$link .= SID_ARG_2ND_NOT_ENCODED;
		}
		
		return $link;
	}
}
?>
