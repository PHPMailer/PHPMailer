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
SyntaxHighlighter.brushes.Bash = function()
{
	var keywords =	'if fi then elif else for do done until while break continue case function return in eq ne gt lt ge le';
	var commands =  'alias apropos awk bash bc bg builtin bzip2 cal cat cd cfdisk chgrp chmod chown chroot' +
					'cksum clear cmp comm command cp cron crontab csplit cut date dc dd ddrescue declare df ' +
					'diff diff3 dig dir dircolors dirname dirs du echo egrep eject enable env ethtool eval ' +
					'exec exit expand export expr false fdformat fdisk fg fgrep file find fmt fold format ' +
					'free fsck ftp gawk getopts grep groups gzip hash head history hostname id ifconfig ' +
					'import install join kill less let ln local locate logname logout look lpc lpr lprint ' +
					'lprintd lprintq lprm ls lsof make man mkdir mkfifo mkisofs mknod more mount mtools ' +
					'mv netstat nice nl nohup nslookup open op passwd paste pathchk ping popd pr printcap ' +
					'printenv printf ps pushd pwd quota quotacheck quotactl ram rcp read readonly renice ' +
					'remsync rm rmdir rsync screen scp sdiff sed select seq set sftp shift shopt shutdown ' +
					'sleep sort source split ssh strace su sudo sum symlink sync tail tar tee test time ' +
					'times touch top traceroute trap tr true tsort tty type ulimit umask umount unalias ' +
					'uname unexpand uniq units unset unshar useradd usermod users uuencode uudecode v vdir ' +
					'vi watch wc whereis which who whoami Wget xargs yes'
					;
    
	this.regexList = [
		{ regex: SyntaxHighlighter.regexLib.singleLinePerlComments,		css: 'comments' },		// one line comments
		{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,			css: 'string' },		// double quoted strings
		{ regex: new RegExp(this.getKeywords(keywords), 'gm'),			css: 'keyword' },		// keywords
		{ regex: new RegExp(this.getKeywords(commands), 'gm'),			css: 'functions' }		// commands
		];
}

SyntaxHighlighter.brushes.Bash.prototype	= new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.Bash.aliases		= ['bash', 'shell'];

