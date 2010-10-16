<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/page/util/menu/PageMenu.class.php');
require_once(WCF_DIR.'lib/page/util/menu/GmapMenu.class.php');

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
}
?>
