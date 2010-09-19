<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Returns the abstract page for for the Gooogle Map
 *
 * @package     de.gmap.wcf.data.page
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapPage extends AbstractPage {
	public $templateName = 'MapPage';

         /**
         * @see Page::assignVariables()
         */
        public function assignVariables() {
                parent::assignVariables();

		WCF::getTPL()->assign(array(
			'allowSpidersToIndexThisPage' => true
		));
        }

	/**
	 * @see Page::show()
	 */
	public function show() {

		// set active header menu item
		require_once(WCF_DIR.'lib/page/util/menu/HeaderMenu.class.php');
		HeaderMenu::setActiveMenuItem('wcf.header.menu.map');

		parent::show();
	}
}
?>
