/**
               _ _____           _          _     _      
              | |  __ \         (_)        | |   | |     
      ___ ___ | | |__) |___  ___ _ ______ _| |__ | | ___ 
     / __/ _ \| |  _  // _ \/ __| |_  / _` | '_ \| |/ _ \
    | (_| (_) | | | \ \  __/\__ \ |/ / (_| | |_) | |  __/
     \___\___/|_|_|  \_\___||___/_/___\__,_|_.__/|_|\___|
	 
	v 1.2 - a jQuery plugin by Alvaro Prieto Lauroba
	
	Licences: MIT & GPL
	Feel free to use or modify this plugin as far as my full name is kept	
	
	If you are going to use this plugin in production environments it is 
	strongly recomended to use its minified version: colResizable.min.js

*/

(function($){ 	
	
	var d = $(document); 		//window object
	var h = $("head");			//head object
	var drag = null;			//reference to the current grip that is being dragged
	var tables = new Object();	//array of the already processed tables (table.id as key)
	var	count = 0;				//internal count to create unique IDs when needed.	
	
	//common strings for minification	(in the minified version there are plenty more)
	var ID = "id";	
	var PX = "px";
	var SIGNATURE ="JColResizer";
	
	//shortcuts
	var I = parseInt;
	var M = Math;
	var ie =$.browser.msie;
	
	//append required CSS rules  
	h.append("<style type='text/css'>  .JColResizer{table-layout:fixed;} .JColResizer td, .JColResizer th{overflow:hidden;}  .JCLRgrips{ height:0px; position:relative;} .JCLRgrip{margin-left:-5px; position:absolute; z-index:5; } .JCLRgrip .JColResizer{position:absolute;background-color:red;filter:alpha(opacity=1);opacity:0;width:10px;cursor:e-resize;height:100%;top:0px} .JCLRLastGrip{position:absolute; width:0px; } .JCLRgripDrag{ border-left:none;	}</style>");

	
	/**
	 * Function to allow column resizing for table objects. It is the starting point to apply the plugin.
	 * @param {DOM node} tb - refrence to the DOM table object to be enhanced
	 * @param {Object} options	- some customization values
	 */
	var init = function( tb, options){
		var t = $(tb);								//the table object is wrapped
		if(options.disable) return destroy(t);		//the user is asking to destroy a previously colResized table
		var	id = t.attr(ID) || SIGNATURE+count++;	//its id is obtained, if null new one is generated
		if(!t.is("table") || tables[id]) return; 	//if the object is not a table or if it was already processed then it is ignored.
		t.addClass(SIGNATURE).attr(ID, id).before('<div class="JCLRgrips"></div>');	//the grips container object is added. Signature class forces table rendering in fixed-layout mode to prevent column's min-width
		t.opt = options; t.g = []; t.c = []; t.col=[]; t.w = t.width(); t.gc = t.prev();	//t.c and t.g are arrays of columns and grips respectively				
		t.cs = I(ie? tb.cellSpacing || tb.currentStyle.borderSpacing :t.css('border-spacing'))||2;	//table cellspacing (not even jQuery is fully cross-browser)
		t.b  = I(ie? tb.border || tb.currentStyle.borderLeftWidth :t.css('border-left-width'))||1;	//outer border width (again cross-browser isues)
		// if(!(tb.style.width || tb.width)) t.width(t.width()); //I am not an IE fan, but it is a pitty that only IE has the currentStyle attribute working as expected. For this reason I can not check easily if the table has an explicit width or if it is rendered as "auto"
		tables[id] = t; 	//the table object is stored using its id as key		
		createGrips(t);		//grips are created
	};


	/**
	 * This function allows to remove any enhancements performed by this plugin on a previously processed table.
	 * @param {jQuery ref} t - table object
	 */
	var destroy = function(t){
		var id=t.attr(ID), t=tables[id];		//its table object is found
		if(!t||!t.is("table")) return;			//if none, then it wasnt processed	 
		t.removeClass(SIGNATURE).gc.remove();	//class and grips are removed
		// delete tables;
		tables[id] = null;						//clean up data
		delete t;
	};

	// var cssWidth = function(obj) {
		// var width = obj.css('width');
		// return I(width.replace('px','').replace('%',''));
	// }

	/**
	 * Function to create all the grips associated with the table given by parameters 
	 * @param {jQuery ref} t - table object
	 */
	var createGrips = function(t){	
		var th = t.find("tr:first th");				//find all the column headers of the current table
		var col = t.find("col");				//find all the column headers of the current table
		if(!th.length) th = t.find("tr:first td"); 	//if none, the first TD row is used		
		t.ln = th.length;							//table length is stored	
		th.each(function(i){						//iterate through the table column headers			
			var c = $(this); 						//jquery wrap for the current column			
			var g = $(t.gc.append('<div class="JCLRgrip"></div>')[0].lastChild); //add the visual node to be used as grip
			g.t = t; g.i = i; g.c = c;
			c.w = I($(col[i]).attr('width')); //-(c.css('border-left-width').replace('px',''))-(c.css('border-right-width').replace('px',''));		//some values are stored in the grip's node data
			// if(c.w == 0) {
				// console.debug($(col[i]));
				// var matches = $(col[i]).attr('style').match(/width:( ?\d+)px;/);
				// if(matches.length)
					// c.w = I(matches[1]);
			// }
			
			
			t.g.push(g); t.c.push(c); t.col.push(col[i]);		//the current grip and column are added to its table object
			// c.width(c.w).removeAttr('width');				//the width of the column is converted into pixel-based measurements
			// c.width(c.w);
			// console.debug(c.w);
			$(col[i]).attr('width',c.w);
			if (i < t.ln-1)	g.mousedown(onGripMouseDown).append(t.opt.gripInnerHtml).append('<div class="'+SIGNATURE+'"></div>'); //bind the mousedown event to start dragging 
			else g.addClass("JCLRLastGrip").removeClass("JCLRgrip");	//the last grip is used only to store data			
			g.data(SIGNATURE, {i:i, t:t.attr(ID)});						//grip index and its table name are stored in the HTML 												
		}); 	
		syncGrips(t); //the grips are positioned according to the current table layout			
		//there is a small problem, some cells in the table could contain dimension values interfering with the 
		//width value set by this plugin. Those values are removed
		t.find('tr:not(:first)').find('td, th, col').each(function(){
			$(this).removeAttr('width');		 
		});		
	};
	
	
	
	/**
	 * Function that places each grip in the correct position according to the current table layout	 * 
	 * @param {jQuery ref} t - table object
	 */
	var syncGrips = function (t){	
		t.gc.width(t.w);			//the grip's container width is updated 		
		for(var i=0; i<t.ln; i++){	//for each column
			var c = t.c[i]; 			
			t.g[i].css({			//height and position of the grip is updated according to the table layout
				left: c.offset().left - t.offset().left + c.outerWidth() + t.cs / 2 + PX,
				height: t.opt.headerOnly? t.c[0].outerHeight() : t.outerHeight()
			});												
		} 	
	};
	
	
	/**
	* This function updates column's width according to the horizontal position increment of the grip being
	* dragged. The function can be called while dragging if liveDragging is enabled and also from the onGripDragOver
	* event handler to synchronize grip's position with their related columns.
	* @param {jQuery ref} t - table object
	* @param {nunmber} i - index of the grip being dragged
	* @param {bool} isOver - to identify when the function is being called from the onGripDragOver event	
	*/
	var syncCols = function(t,i,isOver){
		var inc = drag.x-drag.l, c = t.c[i], c2 = t.c[i+1], col = $(t.col[i]), col2 = $(t.col[i+1]);
		var w = c.w + inc;	var w2= c2.w- inc;	//their new width is obtained		
		// c.css('width', w + PX);	c2.css('width',w2 + PX);	//and set	
		col.attr('width',w); if(i<t.ln-1) col2.attr('width', w2);
		if(isOver){c.w=w; c2.w=w2;}
	
	
/* 		var inc = drag.x-drag.l, col = $(t.col[i]), col2 = $(t.col[i+1]);
		var w = t.c[i].w + inc;	var w2= t.c[i].w- inc;	//their new width is obtained		
		// c.css('width', w + PX);	c2.css('width',w2 + PX);	//and set
		col.css('width', w + PX); if(i<t.ln-1) col2.css('width', w2 + PX);
		if(isOver){col.w=w; col2.w=w2;} */
	};

	
	/**
	 * Event handler used while dragging a grip. It checks if the next grip's position is valid and updates it. 
	 * @param {event} e - mousemove event binded to the window object
	 */
	var onGripDrag = function(e){	
		if(!drag) return; var t = drag.t;		//table object reference 
		var x = e.pageX - drag.ox + drag.l;		//next position according to horizontal mouse position increment
		var mw = t.opt.minWidth, i = drag.i ;	//cell's min width
		var l = t.cs*1.5 + mw + t.b;
		
		var max = i == t.ln-1? t.w-l: t.g[i+1].position().left-t.cs-mw; //max position according to the contiguous cells
		var min = i? t.g[i-1].position().left+t.cs+mw: l;				//min position according to the contiguous cells
		
		x = M.max(min, M.min(max, x));						//apply boundings		
		drag.x = x;	 drag.css("left",  x + PX); 			//apply position increment				
		if(t.opt.liveDrag){ 								//if liveDrag is enabled
			syncCols(t,i); syncGrips(t);					//columns and grips are synchronized
			var cb = t.opt.onDrag;							//check if there is an onDrag callback
			if (cb) { e.currentTarget = t[0]; cb(e); }		//if any, it is fired			
		}
		
		return false; 	//prevent text selection				
	};
	

	/**
	 * Event handler fired when the dragging is over, updating table layout
	 */
	var onGripDragOver = function(e){	
		
		d.unbind('mousemove.'+SIGNATURE).unbind('mouseup.'+SIGNATURE);
		$("head :last-child").remove(); 				//remove the dragging cursor style	
		if(!drag) return;
		drag.removeClass(drag.t.opt.draggingClass);		//remove the grip's dragging css-class
		var t = drag.t, cb = t.opt.onResize; 			//get some values	
		if(drag.x){ 									//only if the column width has been changed
			syncCols(t,drag.i, true);	syncGrips(t);	//the columns and grips are updated
			if (cb) { e.currentTarget = t[0]; cb(e); }	//if there is a callback function, it is fired
		}	
		drag = null;									//since the grip's dragging is over									
	};	
	

	/**
	 * Event handler fired when the grip's dragging is about to start. Its main goal is to set up events 
	 * and store some values used while dragging.
	 * @param {event} e - grip's mousedown event
	 */
	var onGripMouseDown = function(e){
		var o = $(this).data(SIGNATURE);			//retrieve grip's data
		var t = tables[o.t],  g = t.g[o.i];			//shortcuts for the table and grip objects
		g.ox = e.pageX;	g.l = g.position().left;	//the initial position is kept				
		d.bind('mousemove.'+SIGNATURE, onGripDrag).bind('mouseup.'+SIGNATURE,onGripDragOver);	//mousemove and mouseup events are bound
		h.append("<style type='text/css'>*{cursor:e-resize!important}</style>"); 				//change the mouse cursor to the horizontal-resizing pointer
		g.addClass(t.opt.draggingClass); 	//add the dragging class (to allow some visual feedback)				
		drag = g;							//the current grip is stored as the current dragging object
		if(t.c[o.i].l) for(var i=0,c; i<t.ln; i++){ c=t.c[i]; c.l = false; c.w=c.width(); } 	//if the colum is locked (after browser resize), then c.w must be updated		
		return false; 	//prevent text selection
	};
	
	/**
	 * Event handler fired when the browser is resized. The main purpose of this function is to update
	 * table layout according to the browser's size synchronizing related grips 
	 */
	var onResize = function(){
		for(t in tables){		
			var t = tables[t], i, mw=0;				
			t.removeClass(SIGNATURE);						//firefox doesnt like layout-fixed in some cases
			if (t.w != t.width()) {							//if the the table's width has changed
				t.w = t.width();							//its new value is kept
				for(i=0; i<t.ln; i++) mw+= t.c[i].w;		//the active cells area is obtained
				//cell rendering is not as trivial as it might seem, and it is slightly different for
				//each browser. In the begining i had a big switch for each browser, but since the code
				//was extremelly ugly now I use a different approach with several reflows. This works 
				//pretty well but it's a bit slower. For now, lets keep things simple...   
				for(i=0; i<t.ln; i++) t.c[i].css("width", M.round(1000*t.c[i].w/mw)/10 + "%").l=true; 
				//c.l locks the column, telling us that its c.w is outdated									
			}
			syncGrips(t.addClass(SIGNATURE));
		}
	};


	//bind resize event, to update grips position 
	$(window).bind('resize.'+SIGNATURE, onResize); 


	/**
	 * The plugin is added to the jQuery library
	 * @param {Object} options -  an object containg some basic customization values 
	 */
	$.fn.extend({  
		colResizable: function(options) {           
			var defaults = {
			
				//attributes:
				draggingClass: 'JCLRgripDrag',	//css-class used when a grip is being dragged (for visual feedback purposes)
				gripInnerHtml: '',				//if it is required to use a custom grip it can be done using some custom HTML				
				liveDrag: false,				//enables table-layout updaing while dragging			
				minWidth: 15, 					//minimum width value in pixels allowed for a column 
				headerOnly: false,				//specifies that the size of the the column resizing anchors will be bounded to the size of the first row 
				disable: false,					//disables all the enhancements performed in a previously colResized table	
				
				//events:
				onDrag: null, 					//callback function to be fired during the column resizing process if liveDrag is enabled
				onResize: null					//callback function fired when the dragging process is over
			}			
			var options =  $.extend(defaults, options);			
			return this.each(function() {				
				init( this, options);             
			});
		}
	});
})(jQuery);

