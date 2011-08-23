/**
 * SyntaxHighlighter
 * http://alexgorbatchev.com/
 *
 * SyntaxHighlighter is donationware. If you are using it, please donate.
 * http://alexgorbatchev.com/wiki/SyntaxHighlighter:Donate
 *
 * @version
 * 2.0.296 (March 01 2009)
 * 
 * @copyright
 * Copyright (C) 2004-2009 Alex Gorbatchev.
 *
 * @license
 * This file is part of SyntaxHighlighter.
 * 
 * SyntaxHighlighter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * SyntaxHighlighter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with SyntaxHighlighter.  If not, see <http://www.gnu.org/licenses/>.
 */
//
// Begin anonymous function. This is used to contain local scope variables without polutting global scope.
//
if (!window.SyntaxHighlighter) var SyntaxHighlighter = function() { 

// Shortcut object which will be assigned to the SyntaxHighlighter variable.
// This is a shorthand for local reference in order to avoid long namespace 
// references to SyntaxHighlighter.whatever...
var sh = {
	defaults : {
		/** Additional CSS class names to be added to highlighter elements. */
		'class-name' : '',
		
		/** First line number. */
		'first-line' : 1,
		
		/** Font size of the SyntaxHighlighter block. */
		'font-size' : null,
		
		/** Lines to highlight. */
		'highlight' : null,
		
		/** Enables or disables smart tabs. */
		'smart-tabs' : true,
		
		/** Gets or sets tab size. */
		'tab-size' : 4,
		
		/** Enables or disables ruler. */
		'ruler' : false,
		
		/** Enables or disables gutter. */
		'gutter' : true,
		
		/** Enables or disables toolbar. */
		'toolbar' : true,
		
		/** Forces code view to be collapsed. */
		'collapse' : false,
		
		/** Enables or disables automatic links. */
		'auto-links' : true,
		
		/** Gets or sets light mode. Equavalent to turning off gutter and toolbar. */
		'light' : false
	},
	
	config : {
		/** Path to the copy to clipboard SWF file. */
		clipboardSwf : null,

		/** Width of an item in the toolbar. */
		toolbarItemWidth : 16,

		/** Height of an item in the toolbar. */
		toolbarItemHeight : 16,
		
		/** Blogger mode flag. */
		bloggerMode : false,
		
		/** Name of the tag that SyntaxHighlighter will automatically look for. */
		tagName : 'pre',
		
		strings : {
			expandSource : 'expand source',
			viewSource : 'view source',
			copyToClipboard : 'copy to clipboard',
			copyToClipboardConfirmation : 'The code is in your clipboard now',
			print : 'print',
			help : '?',
			alert: 'SyntaxHighlighter\n\n',
			noBrush : 'Can\'t find brush for: ',
			brushNotHtmlScript : 'Brush wasn\'t configured for html-script option: ',
			
			// this is populated by the build script
			aboutDialog : '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>About SyntaxHighlighter</title></head><body style="font-family:Georgia,\'Times New Roman\',Times,serif;background-color:#fff;color:#000;font-size:1em;text-align:center;"><div style="text-align:center;margin-top:3em;"><div style="font-family:Geneva,Arial,Helvetica,sans-serif;font-size:xx-large;">SyntaxHighlighter</div><div style="font-size:.75em;margin-bottom:4em;"><div>version 2.0.296 (March 01 2009)</div><div><a href="http://alexgorbatchev.com" target="_blank" style="color:#0099FF;text-decoration:none;">http://alexgorbatchev.com</a></div></div><div>JavaScript code syntax highlighter.</div><div>Copyright 2004-2009 Alex Gorbatchev.</div></div></body></html>'
		},

		/** If true, output will show HTML produces instead. */
		debug : false
	},
	
	/** Internal 'global' variables. */
	vars : {
		discoveredBrushes : null,
		spaceWidth : null,
		printFrame : null,
		highlighters : {}
	},
	
	/** This object is populated by user included external brush files. */		
	brushes : {},

	/** Common regular expressions. */
	regexLib : {
		multiLineCComments			: /\/\*[\s\S]*?\*\//gm,
		singleLineCComments			: /\/\/.*$/gm,
		singleLinePerlComments		: /#.*$/gm,
		doubleQuotedString			: /"(?:\.|(\\\")|[^\""\n])*"/g,
		singleQuotedString			: /'(?:\.|(\\\')|[^\''\n])*'/g,
		multiLineDoubleQuotedString	: /"(?:\.|(\\\")|[^\""])*"/g,
		multiLineSingleQuotedString	: /'(?:\.|(\\\')|[^\''])*'/g,
		url							: /\w+:\/\/[\w-.\/?%&=]*/g,
		
		/** <?= ?> tags. */
		phpScriptTags 				: { left: /(&lt;|<)\?=?/g, right: /\?(&gt;|>)/g },
		
		/** <%= %> tags. */
		aspScriptTags				: { left: /(&lt;|<)%=?/g, right: /%(&gt;|>)/g },
		
		/** <script></script> tags. */
		scriptScriptTags			: { left: /(&lt;|<)\s*script.*?(&gt;|>)/gi, right: /(&lt;|<)\/\s*script\s*(&gt;|>)/gi }
	},

	toolbar : {
		/**
		 * Creates new toolbar for a highlighter.
		 * @param {Highlighter} highlighter    Target highlighter.
		 */
		create : function(highlighter)
		{
			var div = document.createElement('DIV'),
				items = sh.toolbar.items
				;
			
			div.className = 'toolbar';
			
			for (var name in items) 
			{
				var constructor = items[name],
					command = new constructor(highlighter),
					element = command.create()
					;
				
				highlighter.toolbarCommands[name] = command;
				
				if (element == null)
					continue;
					
				if (typeof(element) == 'string')
					element = sh.toolbar.createButton(element, highlighter.id, name);
				
				element.className += 'item ' + name;
				div.appendChild(element);
			}
			
			return div;
		},
		
		/**
		 * Create a standard anchor button for the toolbar.
		 * @param {String} label			Label text to display.
		 * @param {String} highlighterId	Highlighter ID that this button would belong to.
		 * @param {String} commandName		Command name that would be executed.
		 * @return {Element}				Returns an 'A' element.
		 */
		createButton : function(label, highlighterId, commandName)
		{
			var a = document.createElement('a'),
				style = a.style,
				config = sh.config,
				width = config.toolbarItemWidth,
				height = config.toolbarItemHeight
				;
			
			a.href = '#' + commandName;
			a.title = label;
			a.highlighterId = highlighterId;
			a.commandName = commandName;
			a.innerHTML = label;
			
			if (isNaN(width) == false)
				style.width = width + 'px';

			if (isNaN(height) == false)
				style.height = height + 'px';
			
			a.onclick = function(e)
			{
				try
				{
					sh.toolbar.executeCommand(
						this, 
						e || window.event,
						this.highlighterId, 
						this.commandName
					);
				}
				catch(e)
				{
					sh.utils.alert(e.message);
				}
				
				return false;
			};
			
			return a;
		},
		
		/**
		 * Executes a toolbar command.
		 * @param {Element}		sender  		Sender element.
		 * @param {MouseEvent}	event			Original mouse event object.
		 * @param {String}		highlighterId	Highlighter DIV element ID.
		 * @param {String}		commandName		Name of the command to execute.
		 * @return {Object} Passes out return value from command execution.
		 */
		executeCommand : function(sender, event, highlighterId, commandName, args)
		{
			var highlighter = sh.vars.highlighters[highlighterId], 
				command
				;

			if (highlighter == null || (command = highlighter.toolbarCommands[commandName]) == null) 
				return null;

			return command.execute(sender, event, args);
		},
		
		/** Collection of toolbar items. */
		items : {
			expandSource : function(highlighter)
			{
				this.create = function()
				{
					if (highlighter.getParam('collapse') != true)
						return;
					
					return sh.config.strings.expandSource;
				};
			
				this.execute = function(sender, event, args)
				{
					var div = highlighter.div;
					
					sender.parentNode.removeChild(sender);
					div.className = div.className.replace('collapsed', '');
				};
			},
		
			/** 
			 * Command to open a new window and display the original unformatted source code inside.
			 */
			viewSource : function(highlighter)
			{
				this.create = function()
				{
					return sh.config.strings.viewSource;
				};
				
				this.execute = function(sender, event, args)
				{
					var code = sh.utils.fixForBlogger(highlighter.originalCode).replace(/</g, '&lt;'),
						wnd = sh.utils.popup('', '_blank', 750, 400, 'location=0, resizable=1, menubar=0, scrollbars=1')
						;
					
					code = sh.utils.unindent(code);
					
					wnd.document.write('<pre>' + code + '</pre>');
					wnd.document.close();
				};
			},
			
			/**
			 * Command to copy the original source code in to the clipboard.
			 * Uses Flash method if <code>clipboardSwf</code> is configured.
			 */
			copyToClipboard : function(highlighter)
			{
				var flashDiv, flashSwf,
					highlighterId = highlighter.id
					;
				
				this.create = function()
				{
					var config = sh.config;
					
					// disable functionality if running locally
					if (config.clipboardSwf == null)
						return null;

					function params(list)
					{
						var result = '';
						
						for (var name in list)
							result += "<param name='" + name + "' value='" + list[name] + "'/>";
							
						return result;
					};
					
					function attributes(list)
					{
						var result = '';
						
						for (var name in list)
							result += " " + name + "='" + list[name] + "'";
							
						return result;
					};
					
					var args1 = {
							width				: config.toolbarItemWidth,
							height				: config.toolbarItemHeight,
							id					: highlighterId + '_clipboard',
							type				: 'application/x-shockwave-flash',
							title				: sh.config.strings.copyToClipboard
						},
						
						// these arguments are used in IE's <param /> collection
						args2 = {
							allowScriptAccess	: 'always',
							wmode				: 'transparent',
							flashVars			: 'highlighterId=' + highlighterId,
							menu				: 'false'
						},
						swf = config.clipboardSwf,
						html
					;

					if (/msie/i.test(navigator.userAgent))
					{
						html = '<object'
							+ attributes({
								classid : 'clsid:d27cdb6e-ae6d-11cf-96b8-444553540000',
								codebase : 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0'
							})
							+ attributes(args1)
							+ '>'
							+ params(args2)
							+ params({ movie : swf })
							+ '</object>'
						;
					}
					else
					{
						html = '<embed'
							+ attributes(args1)
							+ attributes(args2)
							+ attributes({ src : swf })
							+ '/>'
						;
					}

					flashDiv = document.createElement('div');
					flashDiv.innerHTML = html;
					
					return flashDiv;
				};
				
				this.execute = function(sender, event, args)
				{
					var command = args.command;

					switch (command)
					{
						case 'get':
							var code = sh.utils.unindent(
								sh.utils.fixForBlogger(highlighter.originalCode)
									.replace(/&lt;/g, '<')
									.replace(/&gt;/g, '>')
									.replace(/&amp;/g, '&')
								);

							if(window.clipboardData)
								// will fall through to the confirmation because there isn't a break
								window.clipboardData.setData('text', code);
							else
								return sh.utils.unindent(code);
							
						case 'ok':
							sh.utils.alert(sh.config.strings.copyToClipboardConfirmation);
							break;
							
						case 'error':
							sh.utils.alert(args.message);
							break;
					}
				};
			},
			
			/** Command to print the colored source code. */
			printSource : function(highlighter)
			{
				this.create = function()
				{
					return sh.config.strings.print;
				};
				
				this.execute = function(sender, event, args)
				{
					var iframe = document.createElement('IFRAME'),
						doc = null
						;
					
					// make sure there is never more than one hidden iframe created by SH
					if (sh.vars.printFrame != null)
						document.body.removeChild(sh.vars.printFrame);
					
					sh.vars.printFrame = iframe;
					
					// this hides the iframe
					iframe.style.cssText = 'position:absolute;width:0px;height:0px;left:-500px;top:-500px;';
				
					document.body.appendChild(iframe);
					doc = iframe.contentWindow.document;
					
					copyStyles(doc, window.document);
					doc.write('<div class="' + highlighter.div.className.replace('collapsed', '') + ' printing">' + highlighter.div.innerHTML + '</div>');
					doc.close();
					
					iframe.contentWindow.focus();
					iframe.contentWindow.print();
					
					function copyStyles(destDoc, sourceDoc)
					{
						var links = sourceDoc.getElementsByTagName('link');
					
						for(var i = 0; i < links.length; i++)
							if(links[i].rel.toLowerCase() == 'stylesheet' && /shCore\.css$/.test(links[i].href))
								destDoc.write('<link type="text/css" rel="stylesheet" href="' + links[i].href + '"></link>');
					};
				};
			},

			/** Command to display the about dialog window. */
			about : function(highlighter)
			{
				this.create = function()
				{	
					return sh.config.strings.help;
				};

				this.execute = function(sender, event)
				{	
					var wnd = sh.utils.popup('', '_blank', 500, 250, 'scrollbars=0'),
						doc = wnd.document
						;
					
					doc.write(sh.config.strings.aboutDialog);
					doc.close();
					wnd.focus();
				};
			}
		}
	},

	utils : {
		guid : function(prefix)
		{
			return prefix + Math.round(Math.random() * 1000000).toString();
		},
		
		/**
		 * Merges two objects. Values from obj2 override values in obj1.
		 * Function is NOT recursive and works only for one dimensional objects.
		 * @param {Object} obj1 First object.
		 * @param {Object} obj2 Second object.
		 * @return {Object} Returns combination of both objects.
		 */
		merge: function(obj1, obj2)
		{
			var result = {}, name;

			for (name in obj1) 
				result[name] = obj1[name];
			
			for (name in obj2) 
				result[name] = obj2[name];
				
			return result;
		},
		
		/**
		 * Attempts to convert string to boolean.
		 * @param {String} value Input string.
		 * @return {Boolean} Returns true if input was "true", false if input was "false" and value otherwise.
		 */
		toBoolean: function(value)
		{
			switch (value)
			{
				case "true":
					return true;
					
				case "false":
					return false;
			}
			
			return value;
		},
		
		/**
		 * Opens up a centered popup window.
		 * @param {String} url		URL to open in the window.
		 * @param {String} name		Popup name.
		 * @param {int} width		Popup width.
		 * @param {int} height		Popup height.
		 * @param {String} options	window.open() options.
		 * @return {Window}			Returns window instance.
		 */
		popup: function(url, name, width, height, options)
		{
			var x = (screen.width - width) / 2,
				y = (screen.height - height) / 2
				;
				
			options +=	', left=' + x + 
						', top=' + y +
						', width=' + width +
						', height=' + height
				;
			options = options.replace(/^,/, '');

			var win = window.open(url, name, options);
			win.focus();
			return win;
		},
		
		/**
		 * Adds event handler to the target object.
		 * @param {Object} obj		Target object.
		 * @param {String} type		Name of the event.
		 * @param {Function} func	Handling function.
		 */
		addEvent: function(obj, type, func)
		{
			if (obj.attachEvent) 
			{
				obj['e' + type + func] = func;
				obj[type + func] = function()
				{
					obj['e' + type + func](window.event);
				}
				obj.attachEvent('on' + type, obj[type + func]);
			}
			else 
			{
				obj.addEventListener(type, func, false);
			}
		},
		
		/**
		 * Displays an alert.
		 * @param {String} str String to display.
		 */
		alert: function(str)
		{
			alert(sh.config.strings.alert + str)
		},
		
		/**
		 * Finds a brush by its alias.
		 *
		 * @param {String} alias	Brush alias.
		 * @param {Boolean} alert	Suppresses the alert if false.
		 * @return {Brush}			Returns bursh constructor if found, null otherwise.
		 */
		findBrush: function(alias, alert)
		{
			var brushes = sh.vars.discoveredBrushes,
				result = null
				;
			
			if (brushes == null) 
			{
				brushes = {};
				
				// Find all brushes
				for (var brush in sh.brushes) 
				{
					var aliases = sh.brushes[brush].aliases;
					
					if (aliases == null) 
						continue;
					
					for (var i = 0; i < aliases.length; i++) 
						brushes[aliases[i]] = brush;
				}
				
				sh.vars.discoveredBrushes = brushes;
			}
			
			result = sh.brushes[brushes[alias]];

			if (result == null && alert != false)
				sh.utils.alert(sh.config.strings.noBrush + alias);
			
			return result;
		},
		
		/**
		 * Executes a callback on each line and replaces each line with result from the callback.
		 * @param {Object} str			Input string.
		 * @param {Object} callback		Callback function taking one string argument and returning a string.
		 */
		eachLine: function(str, callback)
		{
			var lines = str.split('\n');
			
			for (var i = 0; i < lines.length; i++)
				lines[i] = callback(lines[i]);
				
			return lines.join('\n');
		},
		
		/**
		 * Creates rules looking div.
		 */
		createRuler: function()
		{
			var div = document.createElement('div'),
				ruler = document.createElement('div'),
				showEvery = 10,
				i = 1
				;
			
			while (i <= 150) 
			{
				if (i % showEvery === 0) 
				{
					div.innerHTML += i;
					i += (i + '').length;
				}
				else 
				{
					div.innerHTML += '&middot;';
					i++;
				}
			}
			
			ruler.className = 'ruler line';
			ruler.appendChild(div);
	
			return ruler;
		},
	
		/**
		 * This is a special trim which only removes first and last empty lines
		 * and doesn't affect valid leading space on the first line.
		 * 
		 * @param {String} str   Input string
		 * @return {String}      Returns string without empty first and last lines.
		 */
		trimFirstAndLastLines: function(str)
		{
			return str.replace(/^[ ]*[\n]+|[\n]*[ ]*$/g, '');
		},
		
		/**
		 * Parses key/value pairs into hash object.
		 * 
		 * Understands the following formats:
		 * - name: word;
		 * - name: [word, word];
		 * - name: "string";
		 * - name: 'string';
		 * 
		 * For example:
		 *   name1: value; name2: [value, value]; name3: 'value'
		 *   
		 * @param {String} str    Input string.
		 * @return {Object}       Returns deserialized object.
		 */
		parseParams: function(str)
		{
			var match, 
				result = {},
				arrayRegex = new XRegExp("^\\[(?<values>(.*?))\\]$"),
				regex = new XRegExp(
					"(?<name>[\\w-]+)" +
					"\\s*:\\s*" +
					"(?<value>" +
						"[\\w-%#]+|" +		// word
						"\\[.*?\\]|" +		// [] array
						'".*?"|' +			// "" string
						"'.*?'" +			// '' string
					")\\s*;?",
					"g"
				)
				;

			while ((match = regex.exec(str)) != null) 
			{
				var value = match.value
					.replace(/^['"]|['"]$/g, '') // strip quotes from end of strings
					;
				
				// try to parse array value
				if (value != null && arrayRegex.test(value))
				{
					var m = arrayRegex.exec(value);
					value = m.values.length > 0 ? m.values.split(/\s*,\s*/) : [];
				}
				
				result[match.name] = value;
			}
			
			return result;
		},
	
		/**
		 * Wraps each line of the string into <code/> tag with given style applied to it.
		 * 
		 * @param {String} str   Input string.
		 * @param {String} css   Style name to apply to the string.
		 * @return {String}      Returns input string with each line surrounded by <span/> tag.
		 */
		decorate: function(str, css)
		{
			if (str == null || str.length == 0 || str == '\n') 
				return str;
	
			str = str.replace(/</g, '&lt;');
	
			// Replace two or more sequential spaces with &nbsp; leaving last space untouched.
			str = str.replace(/ {2,}/g, function(m)
			{
				var spaces = '';
				
				for (var i = 0; i < m.length - 1; i++)
					spaces += '&nbsp;';
				
				return spaces + ' ';
			});

			// Split each line and apply <span class="...">...</span> to them so that
			// leading spaces aren't included.
			if (css != null) 
				str = sh.utils.eachLine(str, function(line)
				{
					if (line.length == 0) 
						return '';
					
					var spaces = '';
					
					line = line.replace(/^(&nbsp;| )+/, function(s)
					{
						spaces = s;
						return '';
					});
					
					if (line.length == 0) 
						return spaces;
					
					return spaces + '<code class="' + css + '">' + line + '</code>';
				});

			return str;
		},
	
		/**
		 * Pads number with zeros until it's length is the same as given length.
		 * 
		 * @param {Number} number	Number to pad.
		 * @param {Number} length	Max string length with.
		 * @return {String}			Returns a string padded with proper amount of '0'.
		 */
		padNumber : function(number, length)
		{
			var result = number.toString();
			
			while (result.length < length)
				result = '0' + result;
			
			return result;
		},
		
		/**
		 * Measures width of a single space character.
		 * @return {Number} Returns width of a single space character.
		 */
		measureSpace : function()
		{
			var container = document.createElement('div'),
				span,
				result = 0,
				body = document.body,
				id = sh.utils.guid('measureSpace'),
				
				// variable names will be compressed, so it's better than a plain string
				divOpen = '<div class="',
				closeDiv = '</div>',
				closeSpan = '</span>'
				;

			// we have to duplicate highlighter nested structure in order to get an acurate space measurment
			container.innerHTML = 
				divOpen + 'syntaxhighlighter">' 
					+ divOpen + 'lines">' 
						+ divOpen + 'line">' 
							+ divOpen + 'content'
								+ '"><span class="block"><span id="' + id + '">&nbsp;' + closeSpan + closeSpan
							+ closeDiv 
						+ closeDiv 
					+ closeDiv 
				+ closeDiv
				;
			
			body.appendChild(container);
			span = document.getElementById(id);
			
			if (/opera/i.test(navigator.userAgent))
			{
				var style = window.getComputedStyle(span, null);
				result = parseInt(style.getPropertyValue("width"));
			}
			else
			{
				result = span.offsetWidth;
			}

			body.removeChild(container);

			return result;
		},
		
		/**
		 * Replaces tabs with spaces.
		 * 
		 * @param {String} code		Source code.
		 * @param {Number} tabSize	Size of the tab.
		 * @return {String}			Returns code with all tabs replaces by spaces.
		 */
		processTabs : function(code, tabSize)
		{
			var tab = '';
			
			for (var i = 0; i < tabSize; i++)
				tab += ' ';

			return code.replace(/\t/g, tab);
		},
		
		/**
		 * Replaces tabs with smart spaces.
		 * 
		 * @param {String} code    Code to fix the tabs in.
		 * @param {Number} tabSize Number of spaces in a column.
		 * @return {String}        Returns code with all tabs replaces with roper amount of spaces.
		 */
		processSmartTabs : function(code, tabSize)
		{
			var lines = code.split('\n'),
				tab = '\t',
				spaces = ''
				;
			
			// Create a string with 1000 spaces to copy spaces from... 
			// It's assumed that there would be no indentation longer than that.
			for (var i = 0; i < 50; i++) 
				spaces += '                    '; // 20 spaces * 50
					
			// This function inserts specified amount of spaces in the string
			// where a tab is while removing that given tab.
			function insertSpaces(line, pos, count)
			{
				return line.substr(0, pos)
					+ spaces.substr(0, count)
					+ line.substr(pos + 1, line.length) // pos + 1 will get rid of the tab
					;
			};
	
			// Go through all the lines and do the 'smart tabs' magic.
			code = sh.utils.eachLine(code, function(line)
			{
				if (line.indexOf(tab) == -1) 
					return line;
				
				var pos = 0;
				
				while ((pos = line.indexOf(tab)) != -1) 
				{
					// This is pretty much all there is to the 'smart tabs' logic.
					// Based on the position within the line and size of a tab,
					// calculate the amount of spaces we need to insert.
					var spaces = tabSize - pos % tabSize;
					line = insertSpaces(line, pos, spaces);
				}
				
				return line;
			});
			
			return code;
		},
		
		fixForBlogger : function(str)
		{
			return (sh.config.bloggerMode == true) ? str.replace(/<br\s*\/?>|&lt;br\s*\/?&gt;/gi, '\n') : str;
		},
		
		/**
		 * Removes all white space at the begining and end of a string.
		 * 
		 * @param {String} str   String to trim.
		 * @return {String}      Returns string without leading and following white space characters.
		 */
		trim: function(str)
		{
			return str.replace(/\s*$/g, '').replace(/^\s*/, '');
		},
		
		/**
		 * Unindents a block of text by the lowest common indent amount.
		 * @param {String} str   Text to unindent.
		 * @return {String}      Returns unindented text block.
		 */
		unindent: function(str)
		{
			var lines = sh.utils.fixForBlogger(str).split('\n'),
				indents = new Array(),
				regex = /^\s*/,
				min = 1000
				;
			
			// go through every line and check for common number of indents
			for (var i = 0; i < lines.length && min > 0; i++) 
			{
				var line = lines[i];
				
				if (sh.utils.trim(line).length == 0) 
					continue;
				
				var matches = regex.exec(line);
				
				// In the event that just one line doesn't have leading white space
				// we can't unindent anything, so bail completely.
				if (matches == null) 
					return str;
					
				min = Math.min(matches[0].length, min);
			}
			
			// trim minimum common number of white space from the begining of every line
			if (min > 0) 
				for (var i = 0; i < lines.length; i++) 
					lines[i] = lines[i].substr(min);
			
			return lines.join('\n');
		},
	
		/**
		 * Callback method for Array.sort() which sorts matches by
		 * index position and then by length.
		 * 
		 * @param {Match} m1	Left object.
		 * @param {Match} m2    Right object.
		 * @return {Number}     Returns -1, 0 or -1 as a comparison result.
		 */
		matchesSortCallback: function(m1, m2)
		{
			// sort matches by index first
			if(m1.index < m2.index)
				return -1;
			else if(m1.index > m2.index)
				return 1;
			else
			{
				// if index is the same, sort by length
				if(m1.length < m2.length)
					return -1;
				else if(m1.length > m2.length)
					return 1;
			}
			
			return 0;
		},
	
		/**
		 * Executes given regular expression on provided code and returns all
		 * matches that are found.
		 * 
		 * @param {String} code    Code to execute regular expression on.
		 * @param {Object} regex   Regular expression item info from <code>regexList</code> collection.
		 * @return {Array}         Returns a list of Match objects.
		 */ 
		getMatches: function(code, regexInfo)
		{
			function defaultAdd(match, regexInfo)
			{
				return [new sh.Match(match[0], match.index, regexInfo.css)];
			};
			
			var index = 0,
				match = null,
				result = [],
				func = regexInfo.func ? regexInfo.func : defaultAdd
				;
			
			while((match = regexInfo.regex.exec(code)) != null)
				result = result.concat(func(match, regexInfo));
				
			return result;
		},
		
		processUrls: function(code)
		{
			return code.replace(sh.regexLib.url, function(m)
			{
				return '<a href="' + m + '">' + m + '</a>';
			});
		}
	}, // end of utils
	
	/**
	 * Shorthand to highlight all elements on the page that are marked as 
	 * SyntaxHighlighter source code.
	 * 
	 * @param {Object} globalParams		Optional parameters which override element's 
	 * 									parameters. Only used if element is specified.
	 * 
	 * @param {Object} element	Optional element to highlight. If none is
	 * 							provided, all elements in the current document 
	 * 							are highlighted.
	 */ 
	highlight : function(globalParams, element)
	{
		function toArray(source)
		{
			var result = [];
			
			for (var i = 0; i < source.length; i++) 
				result.push(source[i]);
				
			return result;
		};
		
		var elements = element ? [element] : toArray(document.getElementsByTagName(sh.config.tagName)), 
			propertyName = 'innerHTML', 
			highlighter = null
			;

		if (elements.length === 0) 
			return;
	
		for (var i = 0; i < elements.length; i++) 
		{
			var target = elements[i], 
				params = sh.utils.parseParams(target.className),
				brushName
				;

			// local params take precedence over globals
			params = sh.utils.merge(globalParams, params);
			brushName = params['brush'];

			if (brushName == null)
				continue;

			// Instantiate a brush
			if (params['html-script'] == 'true') 
			{
				highlighter = new sh.HtmlScript(brushName);
			}
			else
			{
				var brush = sh.utils.findBrush(brushName);
				
				if (brush)
					highlighter = new brush();
				else
					continue;
			}
			
			highlighter.highlight(target[propertyName], params);
			
			var result = highlighter.div;
			
			if (sh.config.debug) 
			{
				result = document.createElement('textarea');
				result.value = highlighter.div.innerHTML;
				result.style.width = '70em';
				result.style.height = '30em';
			}
			
			target.parentNode.replaceChild(result, target);
		}
	},

	/**
	 * Main entry point for the SyntaxHighlighter.
	 * @param {Object} params Optional params to apply to all highlighted elements.
	 */
	all : function(params)
	{
		sh.utils.addEvent(
			window,
			'load',
			function() { sh.highlight(params); }
		);
	}
}; // end of sh

/** Match object */
sh.Match = function(value, index, css)
{
	this.value = value;
	this.index = index;
	this.length = value.length;
	this.css = css;
};

sh.Match.prototype.toString = function()
{
	return this.value;
};

/**
 * Simulates HTML code with a scripting language embedded.
 * 
 * @param {String} scriptBrushName Brush name of the scripting language.
 */
sh.HtmlScript = function(scriptBrushName)
{
	var scriptBrush = sh.utils.findBrush(scriptBrushName),
		xmlBrush = new sh.brushes.Xml(),
		bracketsRegex = null
		;

	if (scriptBrush == null)
		return;

	scriptBrush = new scriptBrush();	
	this.xmlBrush = xmlBrush;
	
	if (scriptBrush.htmlScript == null)
	{
		sh.utils.alert(sh.config.strings.brushNotHtmlScript + scriptBrushName);
		return;
	}
	
	xmlBrush.regexList.push(
		{ regex: scriptBrush.htmlScript.code, func: process }
	);
	
	function offsetMatches(matches, offset)
	{
		for (var j = 0; j < matches.length; j++) 
			matches[j].index += offset;
	}
	
	function process(match, info)
	{
		var code = match.code,
			matches = [],
			regexList = scriptBrush.regexList,
			offset = match.index + match.left.length,
			htmlScript = scriptBrush.htmlScript,
			result
			;

		for (var i = 0; i < regexList.length; i++)
		{
			result = sh.utils.getMatches(code, regexList[i]);
			offsetMatches(result, offset);
			matches = matches.concat(result);
		}
		
		if (htmlScript.left != null && match.left != null)
		{
			result = sh.utils.getMatches(match.left, htmlScript.left);
			offsetMatches(result, match.index);
			matches = matches.concat(result);
		}
		
		if (htmlScript.right != null && match.right != null)
		{
			result = sh.utils.getMatches(match.right, htmlScript.right);
			offsetMatches(result, match.index + match[0].lastIndexOf(match.right));
			matches = matches.concat(result);
		}
		
		return matches;
	}
};

sh.HtmlScript.prototype.highlight = function(code, params)
{
	this.xmlBrush.highlight(code, params);
	this.div = this.xmlBrush.div;
}

/**
 * Main Highlither class.
 * @constructor
 */
sh.Highlighter = function()
{
};

sh.Highlighter.prototype = {
	/**
	 * Returns value of the parameter passed to the highlighter.
	 * @param {String} name				Name of the parameter.
	 * @param {Object} defaultValue		Default value.
	 * @return {Object}					Returns found value or default value otherwise.
	 */
	getParam : function(name, defaultValue)
	{
		var result = this.params[name];
		return sh.utils.toBoolean(result == null ? defaultValue : result);
	},
	
	/**
	 * Shortcut to document.createElement().
	 * @param {String} name		Name of the element to create (DIV, A, etc).
	 * @return {HTMLElement}	Returns new HTML element.
	 */
	create: function(name)
	{
		return document.createElement(name);
	},
	
	/**
	 * Checks if one match is inside another.
	 * @param {Match} match   Match object to check.
	 * @return {Boolean}      Returns true if given match was inside another, false otherwise.
	 */
	isMatchNested: function(match)
	{
		for (var i = 0; i < this.matches.length; i++)
		{
			var item = this.matches[i];
			
			if (item === null)
				continue;
			
			if ((match.index > item.index) && (match.index < item.index + item.length))
				return true;
		}
		
		return false;
	},
	
	/**
	 * Applies all regular expression to the code and stores all found
	 * matches in the `this.matches` array.
	 * @param {Array} regexList		List of regular expressions.
	 * @param {String} code			Source code.
	 * @return {Array}				Returns list of matches.
	 */
	findMatches: function(regexList, code)
	{
		var result = [];
		
		if (regexList != null)
			for (var i = 0; i < regexList.length; i++) 
				result = result.concat(sh.utils.getMatches(code, regexList[i]));
		
		// sort the matches
		result = result.sort(sh.utils.matchesSortCallback);
	
		return result;
	},
	
	/**
	 * Checks to see if any of the matches are inside of other matches. 
	 * This process would get rid of highligted strings inside comments, 
	 * keywords inside strings and so on.
	 */
	removeNestedMatches: function()
	{
		for (var i = 0; i < this.matches.length; i++)
			if (this.isMatchNested(this.matches[i]))
				this.matches[i] = null;
	},
	
	/**
	 * Splits block of text into individual DIV lines.
	 * @param {String} code     Code to highlight.
	 * @return {String}         Returns highlighted code in HTML form.
	 */
	createDisplayLines : function(code)
	{
		var lines = code.split(/\n/g),
			firstLine = parseInt(this.getParam('first-line')),
			padLength = (firstLine + lines.length).toString().length,
			highlightedLines = this.getParam('highlight', [])
			;
		
		code = '';

		for (var i = 0; i < lines.length; i++)
		{
			var line = lines[i],
				indent = /^(&nbsp;|\s)+/.exec(line),
				lineClass = 'line alt' + (i % 2 == 0 ? 1 : 2),
				lineNumber = sh.utils.padNumber(firstLine + i, padLength),
				highlighted = highlightedLines.indexOf((firstLine + i).toString()) != -1,
				spaces = null
				;

			if (indent != null)
			{
				spaces = indent[0].toString();
				line = line.substr(spaces.length);
				spaces = spaces.replace(/&nbsp;/g, ' ');
				indent = sh.vars.spaceWidth * spaces.length;
			}
			else
			{
				indent = 0;
			}

			line = sh.utils.trim(line);
			
			if (line.length == 0)
				line = '&nbsp;';
			
			if (highlighted)
				lineClass += ' highlighted';
				
			code += 
				'<div class="' + lineClass + '">'
					+ '<code class="number">' + lineNumber + '.</code>'
					+ '<span class="content">'
						+ (spaces != null ? '<code class="spaces">' + spaces.replace(/\s/g, '&nbsp;') + '</code>' : '')
						+ '<span class="block" style="margin-left: ' + indent + 'px !important;">' + line + '</span>'
					+ '</span>'
				+ '</div>'
			;
		}
		
		return code;
	},
	
	/**
	 * Finds all matches in the source code.
	 * @param {String} code		Source code to process matches in.
	 * @param {Array} matches	Discovered regex matches.
	 * @return {String} Returns formatted HTML with processed mathes.
	 */
	processMatches: function(code, matches)
	{
		var pos = 0, 
			result = '',
			decorate = sh.utils.decorate // make an alias to save some bytes
			;
		
		// Finally, go through the final list of matches and pull the all
		// together adding everything in between that isn't a match.
		for (var i = 0; i < matches.length; i++) 
		{
			var match = matches[i];
			
			if (match === null || match.length === 0) 
				continue;
			
			result += decorate(code.substr(pos, match.index - pos), 'plain')
				+ decorate(match.value, match.css)
				;

			pos = match.index + match.length;
		}

		// don't forget to add whatever's remaining in the string
		result += decorate(code.substr(pos), 'plain');

		return result;
	},
	
	/**
	 * Highlights the code and returns complete HTML.
	 * @param {String} code     Code to highlight.
	 * @param {Object} params   Parameters object.
	 */
	highlight: function(code, params)
	{
		var conf = sh.config,
			vars = sh.vars,
			div,
			tabSize
			;

		this.params = {};
		this.div = null;
		this.lines = null;
		this.code = null;
		this.bar = null;
		this.toolbarCommands = {};
		this.id = sh.utils.guid('highlighter_');

		// register this instance in the highlighters list
		vars.highlighters[this.id] = this;

		if (code === null) 
			code = '';

		// Measure width of a single space.
		if (vars.spaceWidth === null)
			vars.spaceWidth = sh.utils.measureSpace();
		
		// local params take precedence over defaults
		this.params = sh.utils.merge(sh.defaults, params || {});

		// process light mode
		if (this.getParam('light') == true)
			this.params.toolbar = this.params.gutter = false;
		
		this.div = div = this.create('DIV');
		this.lines = this.create('DIV');
		this.lines.className = 'lines';

		div.className = 'syntaxhighlighter';
		div.id = this.id;
		
		if (this.getParam('collapse'))
			div.className += ' collapsed';
		
		if (this.getParam('gutter') == false)
			div.className += ' nogutter';

		div.className += ' ' + this.getParam('class-name');
		div.style.fontSize = this.getParam('font-size', ''); // IE7 can't take null
					
		this.originalCode = code;
		this.code = sh.utils.trimFirstAndLastLines(code)
			.replace(/\r/g, ' ') // IE lets these buggers through
			;
		
		tabSize = this.getParam('tab-size');
		
		// replace tabs with spaces
		this.code = this.getParam('smart-tabs') == true
			? sh.utils.processSmartTabs(this.code, tabSize)
			: sh.utils.processTabs(this.code, tabSize)
			;

		this.code = sh.utils.unindent(this.code);

		// add controls toolbar
		if (this.getParam('toolbar')) 
		{
			this.bar = this.create('DIV');
			this.bar.className = 'bar';
			this.bar.appendChild(sh.toolbar.create(this));
			div.appendChild(this.bar);
		}
		
		// add columns ruler
		if (this.getParam('ruler'))
			div.appendChild(sh.utils.createRuler());
	
		div.appendChild(this.lines);
	
		this.matches = this.findMatches(this.regexList, this.code);
		this.removeNestedMatches();
		
		code = this.processMatches(this.code, this.matches);
		
		// finally, split all lines so that they wrap well
		code = this.createDisplayLines(sh.utils.trim(code));
		
		// finally, process the links
		if (this.getParam('auto-links'))
			code = sh.utils.processUrls(code);

		this.lines.innerHTML = code;
	},
	
	/**
	 * Converts space separated list of keywords into a regular expression string.
	 * @param {String} str    Space separated keywords.
	 * @return {String}       Returns regular expression string.
	 */	
	getKeywords: function(str)
	{
		str = str
			.replace(/^\s+|\s+$/g, '')
			.replace(/\s+/g, '\\b|\\b')
			;
		
		return '\\b' + str + '\\b';
	},
	
	/**
	 * Makes a brush compatible with the `html-script` functionality.
	 * @param {Object} regexGroup Object containing `left` and `right` regular expressions.
	 */
	forHtmlScript: function(regexGroup)
	{
		this.htmlScript = {
			left : { regex: regexGroup.left, css: 'script' },
			right : { regex: regexGroup.right, css: 'script' },
			code : new XRegExp(
				"(?<left>" + regexGroup.left.source + ")" +
				"(?<code>.*?)" +
				"(?<right>" + regexGroup.right.source + ")",
				"sgi"
				)
		};
	}
}; // end of Highlighter

return sh;
}(); // end of anonymous function

if (!Array.indexOf)
	/**
	 * Finds an index of element in the array.
	 * @ignore
	 * @param {Object} searchElement
	 * @param {Number} fromIndex
	 * @return {Number} Returns index of element if found; -1 otherwise.
	 */
	Array.prototype.indexOf = function (searchElement, fromIndex)
	{
		fromIndex = Math.max(fromIndex || 0, 0);
		
		for (var i = fromIndex; i < this.length; i++)
			if(this[i] == searchElement)
				return i;
				
		return -1;
	};

/**
 * XRegExp 0.6.1
 * (c) 2007-2008 Steven Levithan
 * <http://stevenlevithan.com/regex/xregexp/>
 * MIT License
 * 
 * provides an augmented, cross-browser implementation of regular expressions
 * including support for additional modifiers and syntax. several convenience
 * methods and a recursive-construct parser are also included.
 */

// prevent running twice, which would break references to native globals
if (!window.XRegExp) {
// anonymous function to avoid global variables
(function () {
// copy various native globals for reference. can't use the name ``native``
// because it's a reserved JavaScript keyword.
var real = {
        exec:    RegExp.prototype.exec,
        match:   String.prototype.match,
        replace: String.prototype.replace,
        split:   String.prototype.split
    },
    /* regex syntax parsing with support for all the necessary cross-
       browser and context issues (escapings, character classes, etc.) */
    lib = {
        part:       /(?:[^\\([#\s.]+|\\(?!k<[\w$]+>|[pP]{[^}]+})[\S\s]?|\((?=\?(?!#|<[\w$]+>)))+|(\()(?:\?(?:(#)[^)]*\)|<([$\w]+)>))?|\\(?:k<([\w$]+)>|[pP]{([^}]+)})|(\[\^?)|([\S\s])/g,
        replaceVar: /(?:[^$]+|\$(?![1-9$&`']|{[$\w]+}))+|\$(?:([1-9]\d*|[$&`'])|{([$\w]+)})/g,
        extended:   /^(?:\s+|#.*)+/,
        quantifier: /^(?:[?*+]|{\d+(?:,\d*)?})/,
        classLeft:  /&&\[\^?/g,
        classRight: /]/g
    },
    indexOf = function (array, item, from) {
        for (var i = from || 0; i < array.length; i++)
            if (array[i] === item) return i;
        return -1;
    },
    brokenExecUndef = /()??/.exec("")[1] !== undefined,
    plugins = {};

/**
 * Accepts a pattern and flags, returns a new, extended RegExp object.
 * differs from a native regex in that additional flags and syntax are
 * supported and browser inconsistencies are ameliorated.
 * @ignore
 */
XRegExp = function (pattern, flags) {
    if (pattern instanceof RegExp) {
        if (flags !== undefined)
            throw TypeError("can't supply flags when constructing one RegExp from another");
        return pattern.addFlags(); // new copy
    }

    var flags           = flags || "",
        singleline      = flags.indexOf("s") > -1,
        extended        = flags.indexOf("x") > -1,
        hasNamedCapture = false,
        captureNames    = [],
        output          = [],
        part            = lib.part,
        match, cc, len, index, regex;

    part.lastIndex = 0; // in case the last XRegExp compilation threw an error (unbalanced character class)

    while (match = real.exec.call(part, pattern)) {
        // comment pattern. this check must come before the capturing group check,
        // because both match[1] and match[2] will be non-empty.
        if (match[2]) {
            // keep tokens separated unless the following token is a quantifier
            if (!lib.quantifier.test(pattern.slice(part.lastIndex)))
                output.push("(?:)");
        // capturing group
        } else if (match[1]) {
            captureNames.push(match[3] || null);
            if (match[3])
                hasNamedCapture = true;
            output.push("(");
        // named backreference
        } else if (match[4]) {
            index = indexOf(captureNames, match[4]);
            // keep backreferences separate from subsequent literal numbers
            // preserve backreferences to named groups that are undefined at this point as literal strings
            output.push(index > -1 ?
                "\\" + (index + 1) + (isNaN(pattern.charAt(part.lastIndex)) ? "" : "(?:)") :
                match[0]
            );
        // unicode element (requires plugin)
        } else if (match[5]) {
            output.push(plugins.unicode ?
                plugins.unicode.get(match[5], match[0].charAt(1) === "P") :
                match[0]
            );
        // character class opening delimiter ("[" or "[^")
        // (non-native unicode elements are not supported within character classes)
        } else if (match[6]) {
            if (pattern.charAt(part.lastIndex) === "]") {
                // for cross-browser compatibility with ECMA-262 v3 behavior,
                // convert [] to (?!) and [^] to [\S\s].
                output.push(match[6] === "[" ? "(?!)" : "[\\S\\s]");
                part.lastIndex++;
            } else {
                // parse the character class with support for inner escapes and
                // ES4's infinitely nesting intersection syntax ([&&[^&&[]]]).
                cc = XRegExp.matchRecursive("&&" + pattern.slice(match.index), lib.classLeft, lib.classRight, "", {escapeChar: "\\"})[0];
                output.push(match[6] + cc + "]");
                part.lastIndex += cc.length + 1;
            }
        // dot ("."), pound sign ("#"), or whitespace character
        } else if (match[7]) {
            if (singleline && match[7] === ".") {
                output.push("[\\S\\s]");
            } else if (extended && lib.extended.test(match[7])) {
                len = real.exec.call(lib.extended, pattern.slice(part.lastIndex - 1))[0].length;
                // keep tokens separated unless the following token is a quantifier
                if (!lib.quantifier.test(pattern.slice(part.lastIndex - 1 + len)))
                    output.push("(?:)");
                part.lastIndex += len - 1;
            } else {
                output.push(match[7]);
            }
        } else {
            output.push(match[0]);
        }
    }

    regex = RegExp(output.join(""), real.replace.call(flags, /[sx]+/g, ""));
    regex._x = {
        source:       pattern,
        captureNames: hasNamedCapture ? captureNames : null
    };
    return regex;
};

/**
 * Barebones plugin support for now (intentionally undocumented)
 * @ignore
 * @param {Object} name
 * @param {Object} o
 */
XRegExp.addPlugin = function (name, o) {
    plugins[name] = o;
};

/**
 * Adds named capture support, with values returned as ``result.name``.
 * 
 * Also fixes two cross-browser issues, following the ECMA-262 v3 spec:
 *  - captured values for non-participating capturing groups should be returned
 *    as ``undefined``, rather than the empty string.
 *  - the regex's ``lastIndex`` should not be incremented after zero-length
 *    matches.
 * @ignore
 */
RegExp.prototype.exec = function (str) {
    var match = real.exec.call(this, str),
        name, i, r2;
    if (match) {
        // fix browsers whose exec methods don't consistently return
        // undefined for non-participating capturing groups
        if (brokenExecUndef && match.length > 1) {
            // r2 doesn't need /g or /y, but they shouldn't hurt
            r2 = new RegExp("^" + this.source + "$(?!\\s)", this.getNativeFlags());
            real.replace.call(match[0], r2, function () {
                for (i = 1; i < arguments.length - 2; i++) {
                    if (arguments[i] === undefined) match[i] = undefined;
                }
            });
        }
        // attach named capture properties
        if (this._x && this._x.captureNames) {
            for (i = 1; i < match.length; i++) {
                name = this._x.captureNames[i - 1];
                if (name) match[name] = match[i];
            }
        }
        // fix browsers that increment lastIndex after zero-length matches
        if (this.global && this.lastIndex > (match.index + match[0].length))
            this.lastIndex--;
    }
    return match;
};
})(); // end anonymous function
} // end if(!window.XRegExp)

/**
 * intentionally undocumented
 * @ignore
 */
RegExp.prototype.getNativeFlags = function () {
    return (this.global     ? "g" : "") +
           (this.ignoreCase ? "i" : "") +
           (this.multiline  ? "m" : "") +
           (this.extended   ? "x" : "") +
           (this.sticky     ? "y" : "");
};

/**
 * Accepts flags; returns a new XRegExp object generated by recompiling
 * the regex with the additional flags (may include non-native flags).
 * The original regex object is not altered.
 * @ignore
 */
RegExp.prototype.addFlags = function (flags) {
    var regex = new XRegExp(this.source, (flags || "") + this.getNativeFlags());
    if (this._x) {
        regex._x = {
            source:       this._x.source,
            captureNames: this._x.captureNames ? this._x.captureNames.slice(0) : null
        };
    }
    return regex;
};

/**
 * Accepts a context object and string; returns the result of calling
 * ``exec`` with the provided string. the context is ignored but is
 * accepted for congruity with ``Function.prototype.call``.
 * @ignore
 */
RegExp.prototype.call = function (context, str) {
    return this.exec(str);
};

/**
 * Accepts a context object and arguments array; returns the result of
 * calling ``exec`` with the first value in the arguments array. the context
 * is ignored but is accepted for congruity with ``Function.prototype.apply``.
 * @ignore
 */
RegExp.prototype.apply = function (context, args) {
    return this.exec(args[0]);
};

/**
 * Accepts a pattern and flags; returns an XRegExp object. if the pattern
 * and flag combination has previously been cached, the cached copy is
 * returned, otherwise the new object is cached.
 * @ignore
 */
XRegExp.cache = function (pattern, flags) {
    var key = "/" + pattern + "/" + (flags || "");
    return XRegExp.cache[key] || (XRegExp.cache[key] = new XRegExp(pattern, flags));
};

/**
 * Accepts a string; returns the string with regex metacharacters escaped.
 * the returned string can safely be used within a regex to match a literal
 * string. escaped characters are [, ], {, }, (, ), -, *, +, ?, ., \, ^, $,
 * |, #, [comma], and whitespace.
 * @ignore
 */
XRegExp.escape = function (str) {
    return str.replace(/[-[\]{}()*+?.\\^$|,#\s]/g, "\\$&");
};

/**
 * Accepts a string to search, left and right delimiters as regex pattern
 * strings, optional regex flags (may include non-native s, x, and y flags),
 * and an options object which allows setting an escape character and changing
 * the return format from an array of matches to a two-dimensional array of
 * string parts with extended position data. returns an array of matches
 * (optionally with extended data), allowing nested instances of left and right
 * delimiters. use the g flag to return all matches, otherwise only the first
 * is returned. if delimiters are unbalanced within the subject data, an error
 * is thrown.
 * 
 * This function admittedly pushes the boundaries of what can be accomplished
 * sensibly without a "real" parser. however, by doing so it provides flexible
 * and powerful recursive parsing capabilities with minimal code weight.
 * 
 * Warning: the ``escapeChar`` option is considered experimental and might be
 * changed or removed in future versions of XRegExp.
 * 
 * unsupported features:
 *  - backreferences within delimiter patterns when using ``escapeChar``.
 *  - although providing delimiters as regex objects adds the minor feature of
 *    independent delimiter flags, it introduces other limitations and is only
 *    intended to be done by the ``XRegExp`` constructor (which can't call
 *    itself while building a regex).
 * 
 * @ignore
 */
XRegExp.matchRecursive = function (str, left, right, flags, options) {
    var options      = options || {},
        escapeChar   = options.escapeChar,
        vN           = options.valueNames,
        flags        = flags || "",
        global       = flags.indexOf("g") > -1,
        ignoreCase   = flags.indexOf("i") > -1,
        multiline    = flags.indexOf("m") > -1,
        sticky       = flags.indexOf("y") > -1,
        /* sticky mode has its own handling in this function, which means you
           can use flag "y" even in browsers which don't support it natively */
        flags        = flags.replace(/y/g, ""),
        left         = left  instanceof RegExp ? (left.global  ? left  : left.addFlags("g"))  : new XRegExp(left,  "g" + flags),
        right        = right instanceof RegExp ? (right.global ? right : right.addFlags("g")) : new XRegExp(right, "g" + flags),
        output       = [],
        openTokens   = 0,
        delimStart   = 0,
        delimEnd     = 0,
        lastOuterEnd = 0,
        outerStart, innerStart, leftMatch, rightMatch, escaped, esc;

    if (escapeChar) {
        if (escapeChar.length > 1) throw SyntaxError("can't supply more than one escape character");
        if (multiline)             throw TypeError("can't supply escape character when using the multiline flag");
        escaped = XRegExp.escape(escapeChar);
        /* Escape pattern modifiers:
            /g - not needed here
            /i - included
            /m - **unsupported**, throws error
            /s - handled by XRegExp when delimiters are provided as strings
            /x - handled by XRegExp when delimiters are provided as strings
            /y - not needed here; supported by other handling in this function
        */
        esc = new RegExp(
            "^(?:" + escaped + "[\\S\\s]|(?:(?!" + left.source + "|" + right.source + ")[^" + escaped + "])+)+",
            ignoreCase ? "i" : ""
        );
    }

    while (true) {
        /* advance the starting search position to the end of the last delimiter match.
           a couple special cases are also covered:
            - if using an escape character, advance to the next delimiter's starting position,
              skipping any escaped characters
            - first time through, reset lastIndex in case delimiters were provided as regexes
        */
        left.lastIndex = right.lastIndex = delimEnd +
            (escapeChar ? (esc.exec(str.slice(delimEnd)) || [""])[0].length : 0);

        leftMatch  = left.exec(str);
        rightMatch = right.exec(str);

        // only keep the result which matched earlier in the string
        if (leftMatch && rightMatch) {
            if (leftMatch.index <= rightMatch.index)
                 rightMatch = null;
            else leftMatch  = null;
        }

        /* paths*:
        leftMatch | rightMatch | openTokens | result
        1         | 0          | 1          | ...
        1         | 0          | 0          | ...
        0         | 1          | 1          | ...
        0         | 1          | 0          | throw
        0         | 0          | 1          | throw
        0         | 0          | 0          | break
        * - does not include the sticky mode special case
          - the loop ends after the first completed match if not in global mode
        */

        if (leftMatch || rightMatch) {
            delimStart = (leftMatch || rightMatch).index;
            delimEnd   = (leftMatch ? left : right).lastIndex;
        } else if (!openTokens) {
            break;
        }

        if (sticky && !openTokens && delimStart > lastOuterEnd)
            break;

        if (leftMatch) {
            if (!openTokens++) {
                outerStart = delimStart;
                innerStart = delimEnd;
            }
        } else if (rightMatch && openTokens) {
            if (!--openTokens) {
                if (vN) {
                    if (vN[0] && outerStart > lastOuterEnd)
                               output.push([vN[0], str.slice(lastOuterEnd, outerStart), lastOuterEnd, outerStart]);
                    if (vN[1]) output.push([vN[1], str.slice(outerStart,   innerStart), outerStart,   innerStart]);
                    if (vN[2]) output.push([vN[2], str.slice(innerStart,   delimStart), innerStart,   delimStart]);
                    if (vN[3]) output.push([vN[3], str.slice(delimStart,   delimEnd),   delimStart,   delimEnd]);
                } else {
                    output.push(str.slice(innerStart, delimStart));
                }
                lastOuterEnd = delimEnd;
                if (!global)
                    break;
            }
        } else {
            // reset lastIndex in case delimiters were provided as regexes
            left.lastIndex = right.lastIndex = 0;
            throw Error("subject data contains unbalanced delimiters");
        }

        // if the delimiter matched an empty string, advance delimEnd to avoid an infinite loop
        if (delimStart === delimEnd)
            delimEnd++;
    }

    if (global && !sticky && vN && vN[0] && str.length > lastOuterEnd)
        output.push([vN[0], str.slice(lastOuterEnd), lastOuterEnd, str.length]);

    // reset lastIndex in case delimiters were provided as regexes
    left.lastIndex = right.lastIndex = 0;

    return output;
};
