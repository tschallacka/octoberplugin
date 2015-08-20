if(typeof window.ExitToolBox === 'undefined') {
	window.ExitToolBox = function() {
	}
}
window.ExitToolBox.prototype.datestrings = {'jan':0,'feb':1,'mar':2,'apr':3,'may':4,'jun':5,'jul':6,'aug':7,'sept':8,'okt':9,'nov':10,'dec':11};
window.ExitToolBox.prototype.makeDatePickerField = function(elem) {
	if(!(elem instanceof jQuery)) {
		var elem = $(elem);
	}
	/** 
	 * Methods to "change" the form values into database format and then change it back afterwards the ajax callw as succesfull
	 */
	elem.closest('form').on('oc.beforeRequest', function(e, data) { if(elem.val().trim().length > 0) {elem.val(that.formatDate('yy-mm-dd',that.dateparse(elem.val())));} });
	elem.closest('form').on('ajaxSuccess', function(e, data) {  if(elem.val().trim().length > 0) {elem.val(that.formatDate('ddMy',that.dateparse(elem.val()))); }});
	elem.closest('form').on('ajaxError',function(e, data) {  if(elem.val().trim().length > 0) {elem.val(that.formatDate('ddMy',that.dateparse(elem.val())));}});
	var that = this;
	if(elem.val().trim().length > 0) {
		elem.val(this.formatDate('ddMy',this.dateparse(elem.val())));
	}
	elem.on('change',function(x,data){
						//console.log(x);
						if(elem.val().trim().length > 0) {
							var runupdate = false;
							if(typeof x.originalEvent !== 'undefined') {
								elem.val(that.formatDate('ddMy',that.dateparse(elem.val())));
							}
						}
					});
	elem.datepicker({
		format:'ddMyy',
		forceParse:false,
		clearBtn:true
	});
	return elem;
		
};
window.ExitToolBox.prototype.dateHasBetween = function(strdate) {
	return !(strdate.indexOf('#') === -1);
}
window.ExitToolBox.prototype.getBetweenDates = function(strdate) {
	var strarr = strdate.split(/#/);
	var ret = {start:null,end:null};
	if(strarr.length === 2) {
		ret.start = this.dateparse(strarr[0]);
		ret.end = this.dateparse(strarr[1]);
	}
	return ret;
	
	
}
window.ExitToolBox.prototype.dateparse = function(strdate) {
	/** 20-jun-2010 */
	if(strdate.length === 10) {
		var s = strdate.split(/-/);
		if(s.length ===3) {
			if(s[0].length !== 4) {
				if(!isNaN(s[2]) && !isNaN(s[1]) && !isNaN(s[0])) {
					return new Date(s[2],s[1]-1,s[0]);
				}
			}
			else {
				if(!isNaN(s[0]) && !isNaN(s[1]) && !isNaN(s[2])) {
					return new Date(s[0],s[1]-1,s[2]);
				}
			}
		}
	}
	/** 2 jun 2010 */
	if(strdate.length === 11 || strdate.length === 10 ) {
		var s = strdate.split(/ /);
		
		if(s.length === 3) {
			if(s[1].trim().length > 0) {
				if(!isNaN(s[0]) && !isNaN(this.datestrings[s[1].toLowerCase()]) && !isNaN(s[2])) {
					return new Date(s[2],this.datestrings[s[1].toLowerCase()],s[0]);
				}
			}
		}
	}
	var today = new Date();
	var tomorrow = new Date();
	tomorrow.setDate(today.getDate() + 1);
	var nextweek = new Date();
	nextweek.setDate(today.getDate()+7);
	if(strdate !== null) {
		var strdatearr = strdate.toString().split(/(?= \+ | \- | \* | \/ )+/);
	}
	else {
		strdatearr = [];
	}
	var runningDate = new Date();
	for(var c=0;c<strdatearr.length;c++) {
		var cur = strdatearr[c].toLowerCase();
		if(cur.indexOf('today') != -1) {
			runningDate = today;
			continue;
		}
		if(cur.indexOf('tomorrow') != -1) {
			runningDate = tomorrow;
			continue;
		}
		if(cur.indexOf('next week') != -1) {
			runningDate = nextweek;
			continue;
		}
		//Matches t+1d or 10may20+31d
		if(cur.match(/^(?:t|[0-9]{1,2}(?:jan|feb|mar|apr|may|jun|jul|aug|sep|okt|nov|dec|[-\/](?:[0-9]{1,2}|jan|feb|mar|apr|may|jun|jul|aug|sep|okt|nov|dec)[-\/])(?:[0-9]{2}|[0-9]{4}))[\+\-][0-9]{1,4}(?:[dmyw]|$)/i)) {
			var denom = cur.charAt(cur.length-1).toLowerCase();
			var start = cur.charAt(0);
			var plus = cur.indexOf('+');
			var min = cur.lastIndexOf('-')
			var number = parseInt(cur.substring((plus !== -1 ? plus : min)+1,cur.length-1));
			if(plus === -1) {
				number = 0-number;
			}
			if(!isNaN(start)) {
				var str = cur.substr(0,plus !== -1 ? plus : min);
				var options = str.split(/-|\//);
				if(options.length ===  3) {
					runningDate = new Date(options[2],options[1]-1,options[0]);
				}				
				else {
					runningDate = new Date(Date.parse(str));
				}
			}
			else {
				// custom default date handlers
				// easely expandable
				switch(start) {
				case "t" : runningDate = today;break;
				default: runningDate = today;break;
				}
				
			}
			//console.log("found it "+runningDate);
			//console.log("testing "+ denom + "with number "+number)
			switch(denom) {
				case "d" :runningDate.setDate(runningDate.getDate()+number);break;
				case "m" :runningDate.setMonth(runningDate.getMonth() + number);break;
				case "w" :runningDate.setDate(runningDate.getDate() + (number*7));break;
				case "y" :runningDate.setFullYear(runningDate.getFullYear()+number);break;
				default:runningDate.setDate(runningDate.getDate()+parseInt(cur.substring((plus !== -1 ? plus : min)+1,cur.length)));break;
				// this is where a "normal date" has been supplied
			}
			//console.log(runningDate);
		}
		var trim = cur.trim(); 
		if(trim.match(/^[0-9]{1,2}(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec|[-\/](?:[0-9]{1,2}|jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[-\/])(?:[0-9]{2}|[0-9]{4})$/i)) {
			var options = trim.split(/-|\//);
			//console.log(options);
			if(options.length ===  3) {
				runningDate = new Date(options[2],options[1]-1,options[0]);
			}				
			else {
				var day = trim.substr(0,2);
				var month = trim.substr(2,3);
				switch(month.toLowerCase()) {
				case 'jan' : month = 0;break;
				case 'feb' : month = 1;break;
				case 'mar' : month = 2;break;
				case 'apr' : month = 3;break;
				case 'may' : month = 4;break;
				case 'jun' : month = 5;break;
				case 'jul' : month = 6;break;
				case 'aug' : month = 7;break;
				case 'sep' : month = 8;break;
				case 'oct' : month = 9;break;
				case 'nov' : month = 10;break;
				case 'dec' : month = 11;break;
				}
				var yr = '20'+trim.substr(-2);
				
				runningDate = new Date(yr,month,day);
			}
		}
		/*var s = cur.split(/-|\//);
		if(s.length == 3 && ) {
				if(!isNaN(s[0]) && !isNaN(s[1]) && !isNaN(s[2])) {
					runningDate =  new Date(s[2],s[1]-1,s[0]);
					continue;
				}
			}
		}*/
		
		if(cur.startsWith(" + ")) {
			
			cur = cur.substring(3,cur.length);
			var tmp = cur.split(/ /);
			var number = parseInt(tmp[0].trim());
			
			if(isNaN(number)) {
				//window.alert(tmp[0] + " in "+cur+" is not a number. This will be skipped.");
				continue;
			}
			var instruction = tmp[1].trim();
			
			if(instruction === 'day' || instruction === 'days') {
				runningDate.setDate(runningDate.getDate() + number);
				
			} 
			if(instruction === 'week' || instruction === 'weeks') {
				runningDate.setDate(runningDate.getDate() + (number*7));
			}
			if(instruction === 'month' || instruction === 'months') {
				runningDate.setMonth(runningDate.getMonth() + number);
			}
			if(instruction === 'year' || instruction === 'year') {
				runningDate.setFullYear(runningDate.getFullYear()+number);
			}
			continue;
		}
		if(cur.startsWith(" - ")) {
			cur = cur.substring(3,cur.length);
			var tmp = cur.split(/ /);
			var number = parseInt(tmp[0]);
			if(isNaN(number)) {
				//window.alert(tmp[0] + " in "+cur+" is not a number. This will be skipped.");
				continue;
			}
			var instruction = tmp[1].trim();
			if(instruction === 'day' || instruction === 'days') {
				runningDate.setDate(runningDate.getDate() - number);
			} 
			if(instruction === 'week' || instruction === 'weeks') {
				runningDate.setDate(runningDate.getDate() - (number*7));
			}
			if(instruction === 'month' || instruction === 'months') {
				runningDate.setMonth(runningDate.setMonth() - number);
			}
			if(instruction === 'year' || instruction === 'year') {
				runningDate.setFullYear(runningDate.getFullYear()-number);
			}
			continue;
		}
		if(cur.startsWith(" / ")) {
			cur = cur.substring(3,cur.length);
			var tmp = cur.split(/ /);
			var number = parseInt(tmp[0]);
			if(isNaN(number)) {
				//window.alert(tmp[0] + " in "+cur+" is not a number. This will be skipped.");
				continue;
			}
			var instruction = tmp[1].trim();
			if(instruction === 'day' || instruction === 'days') {
				runningDate.setDate(runningDate.getDate() / number);
			} 
			if(instruction === 'week' || instruction === 'weeks') {
				runningDate.setDate(runningDate.getDate() / (number*7));
			}
			if(instruction === 'month' || instruction === 'months') {
				runningDate.setMonth(runningDate.setMonth() / number);
			}
			if(instruction === 'year' || instruction === 'year') {
				runningDate.setFullYear(runningDate.getFullYear() / number);
			}
			continue;
		}
		if(cur.startsWith(" * ")) {
			cur = cur.substring(3,cur.length);
			var tmp = cur.split(/ /);
			var number = parseInt(tmp[0]);
			if(isNaN(number)) {
				//window.alert(tmp[0] + " in "+cur+" is not a number. This will be skipped.");
				continue;
			}
			var instruction = tmp[1].trim();
			if(instruction === 'day' || instruction === 'days') {
				runningDate.setDate(runningDate.getDate() * number);
			} 
			if(instruction === 'week' || instruction === 'weeks') {
				runningDate.setDate(runningDate.getDate() * (number*7));
			}
			if(instruction === 'month' || instruction === 'months') {
				runningDate.setMonth(runningDate.setMonth() * number);
			}
			if(instruction === 'year' || instruction === 'year') {
				runningDate.setFullYear(runningDate.getFullYear() * number);
			}
		}
		
	}		
	//window.alert("Calculated date is:\n"+runningDate);
	return runningDate;
	
}
window.ExitToolBox.prototype.displayDate = function(what) {
	try {
		return this.formatDate('ddMy',this.dateparse(what));
	}
	catch(e) {
		return what;
	}
	if(typeof what === 'string' && what != null && what.length > 7) {
		var strp = what.split('-');
		if(strp.length == 3) {
			if(strp[0].length === 4) {
				return this.formatDate('ddMy',new Date(parseInt(strp[0]),parseInt(strp[1])-1,parseInt(strp[2])));
			}
			else {
				return this.formatDate('ddMy',new Date(parseInt(strp[2]),parseInt(strp[1])-1,parseInt(strp[0])));
			}
		}
	}
	else if(typeof what === 'number') {
		return this.formatDate('ddMy',new Date(what));
	}
};
window.ExitToolBox.prototype.formatDate = function (format, date, settings) {
    var _ticksTo1970= (((1970 - 1) * 365 + Math.floor(1970 / 4) - Math.floor(1970 / 100) +
    		Math.floor(1970 / 400)) * 24 * 60 * 60 * 10000000);    
        var iso8601Week =  function(date) {
    		var time,
    			checkDate = new Date(date.getTime());

    		// Find Thursday of this week starting on Monday
    		checkDate.setDate(checkDate.getDate() + 4 - (checkDate.getDay() || 7));

    		time = checkDate.getTime();
    		checkDate.setMonth(0); // Compare with Jan 1
    		checkDate.setDate(1);
    		return Math.floor(Math.round((time - checkDate) / 86400000) / 7) + 1;
    	}
    	var _defaults = { 
    		monthNames: ["January","February","March","April","May","June",
    			"July","August","September","October","November","December"], // Names of months for drop-down and formatting
    		monthNamesShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"], // For formatting
    		dayNames: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"], // For formatting
    		dayNamesShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"], // For formatting
    		dayNamesMin: ["Su","Mo","Tu","We","Th","Fr","Sa"], // Column headings for days starting at Sunday
    		weekHeader: "Wk", // Column header for week of the year
    		dateFormat: "mm/dd/yy", // See format options on parseDate
    	};
    		if (!date) {
    			return "";
    		}

    		var iFormat,
    			dayNamesShort = _defaults.dayNamesShort,
    			dayNames = _defaults.dayNames,
    			monthNamesShort = _defaults.monthNamesShort,
    			monthNames = _defaults.monthNames,
    			// Check whether a format character is doubled
    			lookAhead = function(match) {
    				var matches = (iFormat + 1 < format.length && format.charAt(iFormat + 1) === match);
    				if (matches) {
    					iFormat++;
    				}
    				return matches;
    			},
    			// Format a number, with leading zero if necessary
    			formatNumber = function(match, value, len) {
    				var num = "" + value;
    				if (lookAhead(match)) {
    					while (num.length < len) {
    						num = "0" + num;
    					}
    				}
    				return num;
    			},
    			// Format a name, short or long as requested
    			formatName = function(match, value, shortNames, longNames) {
    				return (lookAhead(match) ? longNames[value] : shortNames[value]);
    			},
    			output = "",
    			literal = false;

    		if (date) {
    			for (iFormat = 0; iFormat < format.length; iFormat++) {
    				if (literal) {
    					if (format.charAt(iFormat) === "'" && !lookAhead("'")) {
    						literal = false;
    					} else {
    						output += format.charAt(iFormat);
    					}
    				} else {
    					switch (format.charAt(iFormat)) {
    						case "d":
    							output += formatNumber("d", date.getDate(), 2);
    							break;
    						case "D":
    							output += formatName("D", date.getDay(), dayNamesShort, dayNames);
    							break;
    						case "o":
    							output += formatNumber("o",
    								Math.round((new Date(date.getFullYear(), date.getMonth(), date.getDate()).getTime() - new Date(date.getFullYear(), 0, 0).getTime()) / 86400000), 3);
    							break;
    						case "m":
    							output += formatNumber("m", date.getMonth() + 1, 2);
    							break;
    						case "M":
    							output += formatName("M", date.getMonth(), monthNamesShort, monthNames);
    							break;
    						case "y":
    							output += (lookAhead("y") ? date.getFullYear() :
    								(date.getYear() % 100 < 10 ? "0" : "") + date.getYear() % 100);
    							break;
    						case "@":
    							output += date.getTime();
    							break;
    						case "!":
    							output += date.getTime() * 10000 + _ticksTo1970;
    							break;
    						case "'":
    							if (lookAhead("'")) {
    								output += "'";
    							} else {
    								literal = true;
    							}
    							break;
    						default:
    							output += format.charAt(iFormat);
    					}
    				}
    			}
    		}
    		return output;
    	}
window.ExitToolBox.prototype.formatDatabaseDate = function($input) {
	return this.formatDate('yy-mm-dd',$input);
}

$(document).ready(function() {
	if(typeof window.ExitTools === 'undefined') {
		window.ExitTools = new window.ExitToolBox();
	}
})
