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
SyntaxHighlighter.brushes.Delphi = function()
{
	var keywords =	'abs addr and ansichar ansistring array as asm begin boolean byte cardinal ' +
					'case char class comp const constructor currency destructor div do double ' +
					'downto else end except exports extended false file finalization finally ' +
					'for function goto if implementation in inherited int64 initialization ' +
					'integer interface is label library longint longword mod nil not object ' +
					'of on or packed pansichar pansistring pchar pcurrency pdatetime pextended ' +
					'pint64 pointer private procedure program property pshortstring pstring ' +
					'pvariant pwidechar pwidestring protected public published raise real real48 ' +
					'record repeat set shl shortint shortstring shr single smallint string then ' +
					'threadvar to true try type unit until uses val var varirnt while widechar ' +
					'widestring with word write writeln xor';

	this.regexList = [
		{ regex: /\(\*[\s\S]*?\*\)/gm,								css: 'comments' },  	// multiline comments (* *)
		{ regex: /{(?!\$)[\s\S]*?}/gm,								css: 'comments' },  	// multiline comments { }
		{ regex: SyntaxHighlighter.regexLib.singleLineCComments,	css: 'comments' },  	// one line
		{ regex: SyntaxHighlighter.regexLib.singleQuotedString,		css: 'string' },		// strings
		{ regex: /\{\$[a-zA-Z]+ .+\}/g,								css: 'color1' },		// compiler Directives and Region tags
		{ regex: /\b[\d\.]+\b/g,									css: 'value' },			// numbers 12345
		{ regex: /\$[a-zA-Z0-9]+\b/g,								css: 'value' },			// numbers $F5D3
		{ regex: new RegExp(this.getKeywords(keywords), 'gm'),		css: 'keyword' }		// keyword
		];
};

SyntaxHighlighter.brushes.Delphi.prototype	= new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.Delphi.aliases	= ['delphi', 'pascal'];
