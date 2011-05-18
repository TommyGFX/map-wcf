/**
 * bb code map, include guard is important!
 *
 * @author      Torben Brodt <easy-coding.de>
 * @url		http://trac.easy-coding.de/trac/wcf/wiki/Gmap
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
if ( typeof BBCodeMap === 'undefined' ) {
	var BBCodeMap = Class.create(Map3, {
		initialize: function($super, lookupClientLocation) {
			// instance counter
			if ( typeof BBCodeMap.counter === 'undefined' ) {
				// It has not... perform the initilization
				BBCodeMap.counter = 0;
			}
			++BBCodeMap.counter;
			
			this.divID = 'bbcodemap-' + BBCodeMap.counter;
			this.lazyInit = function() {
				$super(this.divID, lookupClientLocation);
			};
	
			this.events = [];
		},
	
		/**
		 * document write div containers
		 */
		write: function() {
			document.write('<div id="' + this.divID + '"><div id="' + this.divID + 'Canvas" style="height: 330px"></div></div>');
		},
	});
}
