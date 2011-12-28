<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/page/util/menu/PageMenu.class.php');
require_once(WCF_DIR.'lib/page/util/menu/GmapMenu.class.php');
require_once(WCF_DIR.'lib/data/gmap/GmapApi.class.php');

/**
 * Returns the abstract page for for the Gooogle Map
 *
 * @package     de.gmap.wcf.data.page
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapPage extends AbstractPage {
	public $templateName = 'mapOverview';

         /**
         * @see Page::assignVariables()
         */
        public function assignVariables() {
                parent::assignVariables();
                
                if($this->fromCache('hasFsockopen') == false) {
		        WCF::getTPL()->append('userMessages', '<div class="warning">'.WCF::getLanguage()->get('wcf.map.noConnectivity').'</div>');
		}

		WCF::getTPL()->assign(array(
			'allowSpidersToIndexThisPage' => true,
			'gmapmenu' => GmapMenu::getInstance()
		));
        }

	/**
	 * @see Page::show()
	 */
	public function show() {
		// set active header menu item
		PageMenu::setActiveMenuItem('wcf.header.menu.map');
		
		// set gmap menu to home
		GmapMenu::getInstance()->setActiveMenuItem('wcf.gmap.menu.link.index');

		parent::show();
	}
	
	/**
	 * @return booolean
	 */
	protected function hasFsockopen() {
		try {
			$api = new GmapApi();
			$data = $api->search('berlin, deutschland');
		} catch(Exception $e) {
			$data = array();
		}

		return count($data) > 0;
	}

	/**
	 *
	 */
	protected function fromCache($method, $maxLifetime = 1800, $minLifetime = 0) {
		$key = 'gmap.'.$method;
		
		$cacheResource = array(
			'file' => WCF_DIR.'cache/cache.'.$key.'.php',
			'cache' => $key,
			'minLifetime' => $minLifetime,
			'maxLifetime' => $maxLifetime
		);

		if(($val = WCF::getCache()->getCacheSource()->get($cacheResource)) === null) {
			$val = false;
			$val = $this->$method();
			WCF::getCache()->getCacheSource()->set($cacheResource, $val);
		}
		return $val;
	}

        /**
         * @see Page::show()
         */
        public function show() {

		// skip
		if(!MODULE_GMAP) {
			throw new IllegalLinkException();
		}
        
		parent::show();
        }
}
?>
