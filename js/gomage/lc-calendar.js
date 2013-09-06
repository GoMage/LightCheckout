/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.0
 * @since        Class available since Release 1.0 
 */ 

var LightCheckoutCalendar = {};
LightCheckoutCalendar.setup = function(e) {
	var f = e.trigger;
	if (f) {
		if (typeof f == "string") {
			f = document.getElementById(f)
		} else {
			if (!f.getElementsByTagName) {
				throw "Parameter 'trigger' must be a string or DOM element"
			}
		}
	}
	var d = null;
	if (e.inputField) {
		d = e.inputField;
		if (typeof e.inputField == "string") {
			d = document.getElementById(e.inputField)
		} else {
			if (!d.getElementsByTagName) {
				throw "Parameter 'inputField' must be a string or DOM element"
			}
		}
	}
	var g = null;
	var b = null;
	var a = e.dateFormat || "%m.%d.%Y";
	if (e.min) {
		if (typeof e.min != "string") {
			throw "Parameter 'min' must be a string"
		}
		g = LightCheckoutCalendar.parseDate(e.min, a)
	}
	if (e.max) {
		if (typeof e.max != "string") {
			throw "Parameter 'max' must be a string"
		}
		b = LightCheckoutCalendar.parseDate(e.max, a)
	}
	var addelClass = e.addelClass;
	if (f) {
		var h = new LightCheckoutCalendar.CalendarPopup( {
			triggerEl : f,
			inputField : d,
			dateFormat : e.dateFormat,
			firstDayOfWeek : e.firstDayOfWeek,
			disabled : e.disabled,
			min : g,
			max : b,
			addelClass : addelClass
		});
		h.render();
		return h
	} else {
		throw "Wrong configuration. 'trigger' is required"
	}
};
LightCheckoutCalendar.Calendar = function(a) {
	this.lang = {
		daysShort : [ "Su", "Mo", "Tu", "We", "Th", "Fr", "Sa" ],
		monthsShort : [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ],
		today : "Today",
		goToday : "Go Today"
	};	
	if (typeof LightCheckoutCalendar.Calendar._SDN != "undefined"){		
		var ar = new Array();
        for (var i = 7; i > 0;) {
            ar[--i] = LightCheckoutCalendar.Calendar._SDN[i].substr(0, 2);
        }
        this.lang.daysShort = ar;
	}
	if (typeof LightCheckoutCalendar.Calendar._SMN != "undefined"){
		this.lang.monthsShort = LightCheckoutCalendar.Calendar._SMN;
	}
	if (!a) {
		throw "The class requires a configuration object"
	}
	if (!a.el) {
		throw "'el' is required"
	}
	if (a.firstDayOfWeek) {
		this.firstDayOfWeek = a.firstDayOfWeek
	}
	if (a.disabled) {
		this.disabledFn = a.disabled
	}
	this.max = a.max;
	this.min = a.min;
	this.el = a.el;
	this.value = LightCheckoutCalendar.cloneDate(a.value) || new Date()
};
LightCheckoutCalendar.Calendar.prototype = {
	lang : null,
	el : null,
	elClass : "lc-calendar",
	addelClass: "",
	ctrl : null,
	pane : null,
	value : null,
	disabledFn : null,
	render : function() {
		var a = this.el;		
		a.className = this.elClass + (this.addelClass ? ' ' + this.addelClass : '');
		var c = a.ownerDocument.createElement("DIV");
		a.appendChild(c);
		var b = new LightCheckoutCalendar.Ctrl( {
			el : c,
			firstDayOfWeek : this.firstDayOfWeek,
			lang : this.lang,
			min : this.min,
			max : this.max,
			value : this.value
		});
		var d = a.ownerDocument.createElement("DIV");
		a.appendChild(d);
		var e = new LightCheckoutCalendar.Pane( {
			el : d,
			firstDayOfWeek : this.firstDayOfWeek,
			disabled : this.disabledFn,
			min : this.min,
			max : this.max,
			value : this.value
		});
		b.render();
		e.render();
		this.ctrl = b;
		this.pane = e;
		LightCheckoutCalendar.Event.subscribe(b, "dateSelected",
				this.onDateSelectedFromCtrl, this);
		LightCheckoutCalendar.Event.subscribe(e, "dateSelected",
				this.onDateSelectedFromPane, this)
	},
	onDateSelectedFromCtrl : function() {
		var a = this.ctrl.getValue();
		this.value = a;
		this.pane.setWindowValue(a)
	},
	onDateSelectedFromPane : function() {
		var a = this.pane.getValue();
		this.value = a;
		this.ctrl.setValue(a);
		LightCheckoutCalendar.Event.notify(this, "dateSelected")
	},
	setValue : function(a) {
		a = LightCheckoutCalendar.cloneDate(a);
		if (this.pane) {
			this.pane.setValue(a);
			this.ctrl.setValue(a)
		}
		this.value = a
	},
	getValue : function() {
		return LightCheckoutCalendar.cloneDate(this.value)
	}
};
LightCheckoutCalendar.CalendarPopup = function(a) {
	if (!a.triggerEl) {
		throw "'triggerEl' is required"
	}
	this.triggerEl = a.triggerEl;
	if (a.dateFormat) {
		this.dateFormat = a.dateFormat
	}
	if (this.dateFormat != "%m.%d.%Y" && this.dateFormat != "%d.%m.%Y") {
		throw "Unsupported date format (" + this.dateFormat + ")"
	}
	if (a.inputField) {
		this.inputField = a.inputField
	}
	if (a.firstDayOfWeek) {
		this.firstDayOfWeek = a.firstDayOfWeek
	}
	if (a.disabled) {
		this.disabledFn = a.disabled
	}
	this.min = a.min;
	this.max = a.max;
	if (this.inputField) {
		var b = this.inputField.value;
		if (b && b.length > 0) {
			this.value = LightCheckoutCalendar.parseDate(b, this.dateFormat)
		}
	}
	this.addelClass = a.addelClass;
};
LightCheckoutCalendar.CalendarPopup.prototype = {
	triggerEl : null,
	el : null,
	calendar : null,
	popupElClass : "ls-popup-container",
	popupDisplayed : false,
	dateFormat : "%m.%d.%Y",
	firstDayOfWeek : null,
	inputField : null,
	disabledFn : null,
	min : null,
	max : null,
	value : null,
	addelClass : "",
	render : function() {
		this.initEvents()
	},
	initEvents : function() {
		LightCheckoutCalendar.Event.on(this.triggerEl, "click",
				this.onTriggerClick, this)
	},
	onTriggerClick : function() {
		if (!this.el) {
			this.renderPopup();
			return
		}
		if (!this.popupDisplayed) {
			this.setPopupHidden(false)
		} else {
			this.setPopupHidden(true)
		}
	},
	setPopupHidden : function(a) {
		if (a) {
			this.popupDisplayed = false;
			this.el.style.display = "none"
		} else {
			this.popupDisplayed = true;
			this.el.style.display = "block"
		}
	},
	renderPopup : function() {
		if (!this.el) {
			var e = this.triggerEl.ownerDocument;
			this.el = e.createElement("DIV");
			this.el.className = this.popupElClass;
			var c = this.getBounds(this.triggerEl);
			var a = e.body || e.documentElement;
			a.appendChild(this.el);
			var d = this.el.ownerDocument.createElement("DIV");
			this.el.appendChild(d);
			this.calendar = new LightCheckoutCalendar.Calendar( {
				el : d,
				firstDayOfWeek : this.firstDayOfWeek,
				disabled : this.disabledFn,
				min : this.min,
				max : this.max
			});
			if (this.value) {
				this.calendar.setValue(this.value)
			}
			this.calendar.addelClass = this.addelClass;
			LightCheckoutCalendar.Event.subscribe(this.calendar,
					"dateSelected", this.onDateSelected, this);
			this.calendar.render();
			this.el.style.top = (c.top + c.height) + "px";
			var b = (c.left - d.offsetWidth + c.width);
			if (b < 0) {
				b = 0
			}
			this.el.style.left = b + "px";
			this.el.style.width = d.offsetWidth + "px";
			this.el.style.height = d.offsetHeight + "px";
			this.popupDisplayed = true;
			LightCheckoutCalendar.Event.on(a, "click", this.onBodyClick, this)
		}
	},
	onDateSelected : function() {
		var a = this.calendar.getValue();
		if (this.inputField) {
			this.inputField.value = LightCheckoutCalendar.serializeDate(a,
					this.dateFormat)
		}		
		this.setPopupHidden(true);
		this.reloadTime(a);
	},
	
	reloadTime: function(a){		
		var day = a.getDay();
		if ($('delivery_time')){
			var times = glc_delivery_days[day];
			var value = $('delivery_time').value;
			var selected = false;
			$('delivery_time').options.length = 0;
			for (var i = 0; i < times.length; i++) {
				selected = false;
				if (times[i] == value){
					selected = true;
				}
				$('delivery_time').options[$('delivery_time').options.length] = new Option(glc_time_values[times[i]], times[i], false, selected);
			}
		}
	},
	
	onBodyClick : function(a) {
		if (!this.popupDisplayed) {
			return
		}
		var c = a.target;
		var b = true;
		do {
			if (c === this.triggerEl) {
				b = false;
				break
			}
			if (c.className && c.className.indexOf(this.calendar.elClass) != -1) {
				b = false;
				break
			}
		} while (c = c.parentNode);
		if (b) {
			this.setPopupHidden(true)
		}
	},
	getBounds : function(d) {
		var i = 0, c = 0;
		if (d.getBoundingClientRect) {
			var j = d.getBoundingClientRect(), g = document.body, b = document.documentElement, a = window.pageYOffset
					|| b.scrollTop || g.scrollTop, e = window.pageXOffset
					|| b.scrollLeft || g.scrollLeft, f = b.clientTop
					|| g.clientTop || 0, h = b.clientLeft || g.clientLeft || 0, i = Math
					.round(j.top + a - f), c = Math.round(j.left + e - h)
		} else {
			while (d) {
				i += parseInt(d.offsetTop) || 0;
				c += parseInt(d.offsetLeft) || 0;
				d = d.offsetParent
			}
		}
		return {
			top : i,
			left : c,
			width : d.offsetWidth,
			height : d.offsetHeight
		}
	}
};
LightCheckoutCalendar.Pane = function(b) {
	if (!b.el) {
		throw "'el' is required"
	}
	this.el = b.el;
	if (b.value) {
		this.value = LightCheckoutCalendar.cloneDate(b.value)
	} else {
		this.value = new Date()
	}
	if (b.firstDayOfWeek) {
		this.firstDayOfWeek = b.firstDayOfWeek
	} else {
		this.firstDayOfWeek = 0
	}
	if (b.elClass) {
		this.elClass = b.elClass
	}
	if (b.disabled) {
		this.disabledFn = b.disabled
	}
	if (b.min) {
		var c = LightCheckoutCalendar.cloneDate(b.min);
		c.setHours(0);
		c.setMinutes(0);
		c.setSeconds(0);
		c.setMilliseconds(0);
		this.min = c
	}
	if (b.max) {
		var a = LightCheckoutCalendar.cloneDate(b.max);
		a.setDate(a.getDate() + 1);
		a.setHours(0);
		a.setMinutes(0);
		a.setSeconds(0);
		a.setMilliseconds(-1);
		this.max = a
	}
};
LightCheckoutCalendar.Pane.prototype = {
	el : null,
	elClass : "lc-calendar-pane",
	firstDayOfWeek : 0,
	min : null,
	max : null,
	value : null,
	windowValue : null,
	disabledFn : null,
	render : function() {
		this.renderContent();
		this.initEvents()
	},
	renderContent : function() {
		var j = this.windowValue || this.value;
		var c = LightCheckoutCalendar.cloneDate(j);
		c.setDate(1);
		var f = c.getMonth();
		var e = LightCheckoutCalendar.cloneDate(c);
		e.setMonth(f + 1);
		e.setDate(0);
		var i = 6;
		var a = [];
		this.el.className = this.elClass;
		a.push("<table>");
		var l = [];
		var h = new Date();
		var k = false;
		for ( var m = 0; m < i; m++) {
			a.push("<tr>");
			for ( var b = 0; b < 7; b++) {
				l.length = 0;
				var g = this.getDateAt(b, m, j);
				if (this.disabledFn && this.disabledFn(g) === true) {
					k = true
				} else {
					k = false
				}
				if (this.min && (g < this.min)) {
					k = true
				}
				if (this.max && (g > this.max)) {
					k = true
				}
				if (LightCheckoutCalendar.isTheSameDay(g, this.value)) {
					l.push("lc-selected-day")
				}
				if (LightCheckoutCalendar.isTheSameDay(g, h)) {
					l.push("lc-today")
				}
				if (g.getDay() == 0 || g.getDay() == 6) {
					l.push("lc-weekend")
				}
				if (g.getMonth() != j.getMonth()) {
					l.push("lc-out-of-month")
				}
				if (k) {
					a.push('<td class="lc-disabled-day">')
				} else {
					a.push('<td class="lc-enabled-day">')
				}
				a.push("<div");
				if (l.length) {
					a.push(' class="');
					a.push(l.join(" "));
					a.push('"')
				}
				a.push(' lc-dt="');
				a.push(g.getTime());
				a.push('"');
				a.push(">");
				a.push(g.getDate());
				a.push("</div></td>")
			}
			a.push("</tr>")
		}
		a.push("</table>");
		this.el.innerHTML = a.join("")
	},
	initEvents : function() {
		LightCheckoutCalendar.Event.on(this.el, "click", this.onClick, this)
	},
	onClick : function(a) {
		var c = a.browserEvent;
		if (c.stopPropagation) {
			c.stopPropagation()
		} else {
			c.cancelBubble = true
		}
		var b = a.target;
		var e = b.getAttribute("lc-dt");
		if (!e) {
			return
		}
		e = parseInt(e);
		var f = new Date(e);
		if (this.disabledFn) {
			if (this.disabledFn(f) === true) {
				return
			}
		}
		if (this.min && f < this.min) {
			return
		}
		if (this.max && f > this.max) {
			return
		}
		this.value = f;
		this.renderContent();
		LightCheckoutCalendar.Event.notify(this, "dateSelected")
	},
	getDateAt : function(c, g, a) {
		var f = LightCheckoutCalendar.cloneDate(a);
		f.setDate(1);
		var b = f.getDay() - this.firstDayOfWeek;
		var e = c - b + g * 7;
		f.setTime(f.getTime() + e * 86400000);
		return f
	},
	setValue : function(a) {
		a = LightCheckoutCalendar.cloneDate(a);
		this.value = a;
		this.render()
	},
	setWindowValue : function(a) {
		a = LightCheckoutCalendar.cloneDate(a);
		this.windowValue = a;
		this.render()
	},
	getValue : function() {
		return LightCheckoutCalendar.cloneDate(this.value)
	}
};
LightCheckoutCalendar.Ctrl = function(b) {
	if (!b.el) {
		throw "'el' is required"
	}
	this.el = b.el;
	if (!b.lang) {
		throw "'lang' is required"
	}
	this.lang = b.lang;
	if (b.elClass) {
		this.elClass = b.elClass
	}
	if (b.firstDayOfWeek) {
		this.firstDayOfWeek = b.firstDayOfWeek
	}
	if (b.value) {
		this.value = LightCheckoutCalendar.cloneDate(b.value)
	} else {
		this.value = new Date()
	}
	if (b.min) {
		var c = LightCheckoutCalendar.cloneDate(b.min);
		c.setHours(0);
		c.setMinutes(0);
		c.setSeconds(0);
		c.setMilliseconds(0);
		this.min = c
	}
	if (b.max) {
		var a = LightCheckoutCalendar.cloneDate(b.max);
		a.setDate(a.getDate() + 1);
		a.setHours(0);
		a.setMinutes(0);
		a.setSeconds(0);
		a.setMilliseconds(-1);
		this.max = a
	}
};
LightCheckoutCalendar.Ctrl.prototype = {
	el : null,
	elClass : "lc-calendar-ctrl",
	value : null,
	prevMonth : null,
	nextMonth : null,
	prevYear : null,
	nextYear : null,
	monthTitle : null,
	firstDayOfWeek : null,
	render : function() {
		this.el.className = this.elClass;
		var a = [];
		a
				.push('<div class="lc-calendar-but lc-calendar-y-prev"><div></div></div><div class="lc-calendar-but lc-calendar-m-prev"><div></div></div><div class="lc-calendar-but lc-calendar-m-next"><div></div></div><div class="lc-calendar-but lc-calendar-y-next"><div></div></div><div align="center" class="lc-calendar-but-title">');
		this.generateTitleInnerHtml(a);
		a.push("</div>");
		this.generateWeekDaysHeader(a);
		this.el.innerHTML = a.join("");
		var c = [];
		for ( var b = 0; b < this.el.childNodes.length; b++) {
			if (this.el.childNodes[b].tagName
					&& this.el.childNodes[b].tagName == "DIV") {
				c.push(this.el.childNodes[b])
			}
		}
		this.prevYear = c[0];
		this.prevMonth = c[1];
		this.nextMonth = c[2];
		this.nextYear = c[3];
		this.monthTitle = c[4];
		this.initEvents()
	},
	generateTitleInnerHtml : function(b) {
		var a = this.lang.monthsShort[this.value.getMonth()];
		var c = this.value.getFullYear();
		b.push(a);
		b.push(" ");
		b.push(c)
	},
	initEvents : function() {
		LightCheckoutCalendar.Event.on(this.el, "mouseup", this.onClick, this)
	},
	onClick : function(b) {
		var c = b.target;
		var a = c.className;
		if (a == "" && c.tagName == "DIV") {
			c = c.parentNode;
			a = c.className;
		}
		if (a.indexOf("lc-calendar-y-prev") != -1) {
			this.scrollDate("y", -1)
		} else {
			if (a.indexOf("lc-calendar-m-prev") != -1) {
				this.scrollDate("m", -1)
			} else {
				if (a.indexOf("lc-calendar-m-next") != -1) {
					this.scrollDate("m", 1)
				} else {
					if (a.indexOf("lc-calendar-y-next") != -1) {
						this.scrollDate("y", 1)
					}
				}
			}
		}
	},
	scrollDate : function(c, b) {
		var e = this.value;
		switch (c) {
		case "m":
			e.setMonth(e.getMonth() + b);
			break;
		case "y":
			e.setFullYear(e.getFullYear() + b);
			break
		}
		var a = [];
		this.generateTitleInnerHtml(a);
		this.monthTitle.innerHTML = a.join("");
		LightCheckoutCalendar.Event.notify(this, "dateSelected")
	},
	generateWeekDaysHeader : function(d) {
		d.push('<div class="lc-calendar-week-header"><table><tr>');
		var c = this.lang.daysShort, b;
		var a;
		for ( var e = 0; e < 7; e++) {
			a = e + this.firstDayOfWeek;
			if (a >= 7) {
				a -= 7
			}
			if (a == 0 || a == 6) {
				b = "lc-weekend"
			} else {
				b = null
			}
			if (b) {
				d.push('<td class="');
				d.push(b);
				d.push('">')
			} else {
				d.push("<td>")
			}
			d.push("<div>");
			d.push(c[a]);
			d.push("</div>");
			d.push("</td>")
		}
		d.push("</tr></table></div>")
	},
	getValue : function() {
		return LightCheckoutCalendar.cloneDate(this.value)
	},
	setValue : function(a) {
		a = LightCheckoutCalendar.cloneDate(a);
		this.value = a;
		var b = [];
		this.generateTitleInnerHtml(b);
		this.monthTitle.innerHTML = b.join("")
	}
};
LightCheckoutCalendar.isTheSameDay = function(b, a) {
	return b.getDate() == a.getDate() && b.getMonth() == a.getMonth()
			&& b.getFullYear() == a.getFullYear()
};
LightCheckoutCalendar.cloneDate = function(a) {
	if (!a) {
		return a
	} else {
		return new Date(a.getTime())
	}
};
LightCheckoutCalendar.getWeek = function(c) {
	var a = new Date(c.getFullYear(), 0, 1);
	var b = c.getTime() - a.getTime();
	return Math.ceil(((b / 86400000) + a.getDay() + 1) / 7)
};
LightCheckoutCalendar.parseDate = function(g, e) {
	var b = new Date();
	var c = g.split(".");
	if (e == "%m.%d.%Y") {
		var a = parseInt(c[0]*1) - 1;
		b.setMonth(a);
		var f = parseInt(c[1]*1);
		b.setDate(f);
		var h = parseInt(c[2]*1);
		b.setFullYear(h)
	} else {
		if (e == "%d.%m.%Y") {
			var f = parseInt(c[0]*1);
			b.setDate(f);
			var a = parseInt(c[1]*1) - 1;
			b.setMonth(a);
			var h = parseInt(c[2]*1);
			b.setFullYear(h)
		} else {
			throw "Unsupported format (" + e + ")"
		}
	}
	return b
};
LightCheckoutCalendar.serializeDate = function(b, c) {
	var a = b.getMonth() + 1;
	var e = b.getDate();
	var f = b.getFullYear();
	if (a < 10) {
		a = "0" + a
	}
	if (e < 10) {
		e = "0" + e
	}
	if (c == "%m.%d.%Y") {
		return a + "." + e + "." + f
	} else {
		if (c == "%d.%m.%Y") {
			return e + "." + a + "." + f
		} else {
			throw "Unsupported format (" + c + ")"
		}
	}
};
LightCheckoutCalendar.Event = {};
LightCheckoutCalendar.Event.on = function(c, d, b, a) {
	var e = function(g) {
		var f = {
			target : (g.target || g.srcElement),
			browserEvent : g
		};
		b.apply(a || this, [ f ])
	};
	if (window.addEventListener) {
		c.addEventListener(d, e, false);
		return
	} else {
		if (window.attachEvent) {
			c.attachEvent("on" + d, e);
			return
		}
	}
};
LightCheckoutCalendar.Event.subscribe = function(e, b, c, a) {
	if (!e._listeners) {
		e._listeners = {}
	}
	var d = e._listeners[b];
	if (!d) {
		d = [];
		e._listeners[b] = d
	}
	d.push( {
		listener : c,
		scope : a
	})
};
LightCheckoutCalendar.Event.notify = function(e, c) {
	if (!e._listeners) {
		return
	}
	var d = e._listeners[c];
	if (!d) {
		return
	}
	for ( var a = 0; a < d.length; a++) {
		var b = d[a];
		b.listener.call(b.scope || this)
	}
};