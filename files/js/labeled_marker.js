/*
* LabeledMarker Class, v1.1
* @http://gmaps-utility-library.googlecode.com/svn/trunk/labeledmarker/1.1/src/labeledmarker.js
*
* Copyright 2007 Mike Purvis (http://uwmike.com)
* 
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
* 
*       http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* This class extends the Maps API's standard GMarker class with the ability
* to support markers with textual labels. Please see articles here:
*
*       http://googlemapsbook.com/2007/01/22/extending-gmarker/
*       http://googlemapsbook.com/2007/03/06/clickable-labeledmarker/
*/
function LabeledMarker(a,b){this.latlng_=a;this.opts_=b;this.labelText_=b.labelText||"";this.labelClass_=b.labelClass||"LabeledMarker_markerLabel";this.labelOffset_=b.labelOffset||new GSize(0,0);this.clickable_=b.clickable||true;this.title_=b.title||"";this.labelVisibility_=true;if(b.draggable){b.draggable=false}GMarker.apply(this,arguments)}
LabeledMarker.prototype=new GMarker(new GLatLng(0,0));
LabeledMarker.prototype.initialize=function(c){GMarker.prototype.initialize.apply(this,arguments);this.map_=c;this.div_=document.createElement("div");this.div_.className=this.labelClass_;this.div_.innerHTML=this.labelText_;this.div_.style.position="absolute";this.div_.style.cursor="pointer";this.div_.title=this.title_;c.getPane(G_MAP_MARKER_PANE).appendChild(this.div_);if(this.clickable_){function newEventPassthru(a,b){return function(){GEvent.trigger(a,b)}}var d=['click','dblclick','mousedown','mouseup','mouseover','mouseout'];for(var i=0;i<d.length;i++){var e=d[i];GEvent.addDomListener(this.div_,e,newEventPassthru(this,e))}}}
LabeledMarker.prototype.redraw=function(a){GMarker.prototype.redraw.apply(this,arguments);var p=this.map_.fromLatLngToDivPixel(this.latlng_);var z=GOverlay.getZIndex(this.latlng_.lat());this.div_.style.left=(p.x+this.labelOffset_.width)+"px";this.div_.style.top=(p.y+this.labelOffset_.height)+"px";this.div_.style.zIndex=z}
LabeledMarker.prototype.remove=function(){GEvent.clearInstanceListeners(this.div_);if(this.div_.outerHTML){this.div_.outerHTML=""}if(this.div_.parentNode){this.div_.parentNode.removeChild(this.div_);}this.div_=null;GMarker.prototype.remove.apply(this,arguments)}
LabeledMarker.prototype.copy=function(){return new LabeledMarker(this.latlng_,this.opt_opts_)}
LabeledMarker.prototype.show=function(){GMarker.prototype.show.apply(this,arguments);if(this.labelVisibility_){this.showLabel()}else{this.hideLabel()}}
LabeledMarker.prototype.hide=function(){GMarker.prototype.hide.apply(this,arguments);this.hideLabel()}
LabeledMarker.prototype.setLabelVisibility=function(a){this.labelVisibility_=a;if(!this.isHidden()){if(this.labelVisibility_){this.showLabel()}else{this.hideLabel()}}}
LabeledMarker.prototype.getLabelVisibility=function(){return this.labelVisibility_}
LabeledMarker.prototype.hideLabel=function(){this.div_.style.visibility='hidden'}
LabeledMarker.prototype.showLabel=function(){this.div_.style.visibility='visible'}

