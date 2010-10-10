/**
 * bb code map, include guard is important!
 *
 * @author      Torben Brodt <easy-coding.de>
 * @url		http://trac.easy-coding.de/trac/wcf/wiki/Gmap
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
if ( typeof BBCodeMap === 'undefined' ) {
	var BBCodeMap = function(switchable) {

		// instance counter
		if ( typeof BBCodeMap.counter === 'undefined' ) {
			// It has not... perform the initilization
			BBCodeMap.counter = 0;
		}
		++BBCodeMap.counter;
	
		this.constructor('bbcodemap-' + BBCodeMap.counter, switchable);
	
		this.events = [];
	
		this.registerEvent = function(callback) {
			this.events.push(callback);
		};
	
		/**
		 * document write div containers
		 */
		this.write = function() {
			document.write('<div id="' + this.divID + '"><div id="' + this.divID + 'Canvas" style="height: 300px"></div></div>');
		}
	
		this.runEvents = function() {
			for(var i=0; i<this.events.length; i++) {
				this.events[i]();
			}
		};
	};
	BBCodeMap.prototype = new Map();
}
