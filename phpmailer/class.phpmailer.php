<?php
/////////////////////////////////////////////////
// phpmailer - PHP email class
// 
// Version 0.92, 04/16/2001
//
// Class for sending email using either 
// sendmail, PHP mail(), or SMTP.  Methods are
// based upon the standard AspEmail(tm) classes.
//
// Author: Brent R. Matzelle
//
// License: LGPL, see LICENSE
/////////////////////////////////////////////////

class phpmailer
{
	/////////////////////////////////////////////////
	// CLASS VARIABLES
	/////////////////////////////////////////////////
	
	// General Variables
	var $Priority    = 3;
	var $CharSet     = "iso-8859-1";
	var $ContentType = "text/plain";
	var $Encoding    = "8bit";
	var $From        = "root@localhost";
	var $FromName    = "root";
	var $to          = array();
	var $cc          = array();
	var $bcc         = array();
	var $ReplyTo     = array();
	var $Subject     = "";
	var $Body        = "";
	var $WordWrap    = false;
	var $mailer      = "mail";
	var $sendmail    = "/usr/sbin/sendmail";
	var $attachment  = array();
	var $boundary    = false;
	var $MailerDebug = true;

	// SMTP-specific variables
	var $Host        = "localhost";
	var $Port        = 25;
	var $Helo        = "localhost.localdomain";
	var $Timeout     = 10; // Socket timeout in sec.
	var $SMTPDebug   = false;
	

	/////////////////////////////////////////////////
	// VARIABLE METHODS
	/////////////////////////////////////////////////

	// Sets message to HTML
	function IsHTML($bool) {
		if($bool == true)
			$this->ContentType = "text/html";
		else
			$this->ContentType = "text/plain";
	}

	// Sets mailer to use SMTP
	function IsSMTP() {
		$this->mailer = "smtp";
	}

	// Sets mailer to use PHP mail() function
	function IsMail() {
		$this->mailer = "mail";
	}

	// Sets mailer to directly use $sendmail program
	function IsSendmail() {
		$this->mailer = "sendmail";
	}

	// Sets $sendmail to qmail MTA
	function IsQmail() {
		$this->sendmail = "/var/qmail/bin/qmail-inject";
	}


	/////////////////////////////////////////////////
	// RECIPIENT METHODS
	/////////////////////////////////////////////////	

	// Add a "to" address
	function AddAddress($address, $name = "") {
		$cur = count($this->to);
		$this->to[$cur][0] = trim($address);
		$this->to[$cur][1] = $name;
	}
	
	// Add a "cc" address
	function AddCC($address, $name = "") {
		$cur = count($this->cc);
		$this->cc[$cur][0] = trim($address);
		$this->cc[$cur][1] = $name;
	}
	
	// Add a "bcc" address
	function AddBCC($address, $name = "") {
		$cur = count($this->bcc);
		$this->bcc[$cur][0] = trim($address);
		$this->bcc[$cur][1] = $name;
	}
	
	// Add a "Reply-to" address
	function AddReplyTo($address, $name = "") {
		$cur = count($this->ReplyTo);
		$this->ReplyTo[$cur][0] = trim($address);
		$this->ReplyTo[$cur][1] = $name;
	}


	/////////////////////////////////////////////////
	// MAIL SENDING METHODS
	/////////////////////////////////////////////////
	
	// Create message and assign to mailer
	function Send() {
		if(count($this->to) < 1)
			$this->error_handler("You must provide at least one recipient email address");

		$header = $this->create_header();
		$body = $this->create_body();
		
      // Choose the mailer
		if($this->mailer == "sendmail")
			$this->sendmail_send($header, $body);
		elseif($this->mailer == "mail")
			$this->mail_send($header, $body);
		elseif($this->mailer == "smtp")
			$this->smtp_send($header, $body);
		else
			$this->error_handler(sprintf("%s mailer is not supported", $this->mailer));
	}
	
	// Send using the $sendmail program
	function sendmail_send($header, $body) {
		$sendmail = sprintf("%s -f %s -t", $this->sendmail, $this->From);

		if(!$mail = popen($sendmail, "w"))
			$this->error_handler(sprintf("Could not open %s", $this->sendmail));
		
		fputs($mail, $header);
		fputs($mail, $body);
		pclose($mail);
	}
	
	// Send via the PHP mail() function
	function mail_send($header, $body) {
		// Create mail recipient list
		$to = $this->to[0][0]; // no extra comma
		for($x = 1; $x < count($this->to); $x++)
			$to .= sprintf(",%s", $this->to[$x][0]);
		for($x = 0; $x < count($this->cc); $x++)
			$to .= sprintf(",%s", $this->cc[$x][0]);
		for($x = 0; $x < count($this->bcc); $x++)
			$to .= sprintf(",%s", $this->bcc[$x][0]);
		
		if(!mail($to, $this->Subject, $body, $header))
			$this->error_handler("Could not instantiate mail()");
	}
	
	// Send message via SMTP using PhpSMTP
	// PhpSMTP written by Chris Ryan
	function smtp_send($header, $body) {
		include("class.smtp.php"); // Load code only if asked
		$smtp = new SMTP;
		$smtp->do_debug = $this->SMTPDebug;
		
		// Try to connect to all SMTP servers
		$hosts = explode(";", $this->Host);
		$x = 0;
		$connection = false;
		while($x < count($hosts))
		{
			if($smtp->Connect($hosts[$x], $this->Port, $this->Timeout))
			{
				$connection = true;
				break;
			}
			// printf("%s host could not connect<br>", $hosts[$x]); //debug only
			$x++;
		}
		if(!$connection)
			$this->error_handler("SMTP Error: could not connect to SMTP host server(s)");
	  	  
		$smtp->Hello($this->Helo);
		$smtp->Mail(sprintf("<%s>", $this->From));
		
		for($x = 0; $x < count($this->to); $x++)
			$smtp->Recipient(sprintf("<%s>", $this->to[$x][0]));
		for($x = 0; $x < count($this->cc); $x++)
			$smtp->Recipient(sprintf("<%s>", $this->cc[$x][0]));
		for($x = 0; $x < count($this->bcc); $x++)
			$smtp->Recipient(sprintf("<%s>", $this->bcc[$x][0]));

		$smtp->Data(sprintf("%s%s", $header, $body));
		$smtp->Quit();		
	}
	

	/////////////////////////////////////////////////
	// MESSAGE CREATION METHODS
	/////////////////////////////////////////////////
	
	// Creates recipient headers
	function addr_append($type, $addr) {
		$addr_str = "";
		$addr_str .= sprintf("%s: %s <%s>", $type, $addr[0][1], $addr[0][0]);
		if(count($addr) > 1)
		{
			for($x = 1; $x < count($addr); $x++)
			{
				$addr_str .= sprintf(", %s <%s>", $addr[$x][1], $addr[$x][0]);
			}
			$addr_str .= "\n";
		}
		else
			$addr_str .= "\n";
		
		return($addr_str);
	}
	
	// Wraps message for use with mailers that don't 
	// automatically perform wrapping
	// Written by philippe@cyberabuse.org
	function wordwrap($message, $length) {
		$line=explode("\n", $message);
		$message="";
		for ($i=0 ;$i < count($line); $i++) 
		{
			$line_part = explode(" ", trim($line[$i]));
			$buf = "";
			for ($e = 0; $e<count($line_part); $e++) 
			{
				$buf_o = $buf;
				if ($e == 0)
					$buf .= $line_part[$e];
				else 
					$buf .= " " . $line_part[$e];
				if (strlen($buf) > $length and $buf_o != "")
				{
					$message .= $buf_o . "\n";
					$buf = $line_part[$e];
				}
			}
			$message .= $buf . "\n";
		}
		return ($message);
	}
	
	// Assembles and returns the message header
	function create_header() {
		$header = array();
		$header[] = sprintf("Date: %s\n", date("D M j G:i:s T"));
		$header[] = $this->addr_append("To", $this->to);
		$header[] = sprintf("From: %s <%s>\n", $this->FromName, trim($this->From));
		if(count($this->cc) > 0)
			$header[] = $this->addr_append("cc", $this->cc);
		if(count($this->bcc) > 0)
			$header[] = $this->addr_append("bcc", $this->bcc);
		if(count($this->ReplyTo) > 0)
			$header[] = $this->addr_append("Reply-to", $this->ReplyTo);
		$header[] = sprintf("Subject: %s\n", trim($this->Subject));
		$header[] = sprintf("X-Priority: %d\n", $this->Priority);
		$header[] = sprintf("X-Mailer: phpmailer [version .9]\n");
		$header[] = sprintf("Content-Transfer-Encoding: %s\n", $this->Encoding);
		$header[] = sprintf("Return-Path: %s\n", trim($this->From));
		// $header[] = sprintf("Content-Length: %d\n", (strlen($this->Body) * 7));
		if(count($this->attachment) > 0)
		{
			$header[] = sprintf("Content-Type: Multipart/Mixed; charset = \"%s\";\n", $this->CharSet);
			$header[] = sprintf(" boundary=\"Boundary-=%s\"\n", $this->boundary);
		}
		else
		{
			$header[] = sprintf("Content-Type: %s; charset = \"%s\";\n", $this->ContentType, $this->CharSet);
		}
		$header[] = "MIME-Version: 1.0\n\n";
		
		return(join("", $header));
	}

	// Assembles and returns the message body
	function create_body() {
		// wordwrap the message body if set
		if($this->WordWrap)
			$this->Body = $this->wordwrap($this->Body, $this->WordWrap);

		if(count($this->attachment) > 0)
			$body = $this->attach_all();
		else
			$body = $this->Body;
		
		return($body);		
	}
	
	
	/////////////////////////////////////////////////
	// ATTACHMENT METHODS
	/////////////////////////////////////////////////

	// Check if attachment is valid and add to list			
	function AddAttachment($path) {
		if(!is_file($path))
			$this->error_handler(sprintf("Could not find %s file on filesystem", $path));

		// Separate file name from full path
		$separator = "/";
		$len = strlen($path);
		
		// Set $separator to win32 style
		if(!ereg($separator, $path))
			$separator = "\\";
		
		// Get the filename from the path
		$pos = strrpos($path, $separator) + 1;
		$filename = substr($path, $pos, $len);
		
		// Set message boundary
		$this->boundary = "_b" . md5(uniqid(time()));

		// Append to $attachment array
		$cur = count($this->attachment);		
		$this->attachment[$cur][0] = $path;
		$this->attachment[$cur][1] = $filename;
	}

	// Attach text and binary attachments to body
	function attach_all() {
		// Return text of body
		$mime = array();
		$mime[] = sprintf("--Boundary-=%s\n", $this->boundary);
		$mime[] = sprintf("Content-Type: %s\n", $this->ContentType);
		$mime[] = "Content-Transfer-Encoding: 8bit\n\n";
		$mime[] = sprintf("%s\n", $this->Body);
		
		// Add all attachments
		for($x = 0; $x < count($this->attachment); $x++)
		{
			$path = $this->attachment[$x][0];
			$filename = $this->attachment[$x][1];
			$mime[] = sprintf("--Boundary-=%s\n", $this->boundary);
			$mime[] = "Content-Type: application/octet-stream;\n";
			$mime[] = sprintf("name=\"%s\"\n", $filename);
			$mime[] = "Content-Transfer-Encoding: base64\n";
			$mime[] = sprintf("Content-Disposition: attachment; filename=\"%s\"\n\n", $filename);
			$mime[] = sprintf("%s\n\n", $this->encode_file($path));
		}
		$mime[] = sprintf("\n--Boundary-=%s--\n", $this->boundary);
		
		return(join("", $mime));
	}
	
	// Encode attachment in base64 format
	function encode_file ($path) {
		if(!$fd = fopen($path, "r"))
			$this->error_handler("File Error: Could not open file %s", $path);
		$file = fread($fd, filesize($path));
		
		// chunk_split is found in PHP >= 3.0.6
		$encoded = chunk_split(base64_encode($file));
		fclose($fd);
		
		return($encoded);
	}
	
	/////////////////////////////////////////////////
	// MISCELLANEOUS METHODS
	/////////////////////////////////////////////////

	// Print out error and exit
	function error_handler($msg) {
		if($this->MailerDebug == true)
		{
			print("<h2>Mailer Error</h2>");
			print("Description:<br>");
			printf("<font color=\"FF0000\">%s</font>", $msg);
			exit;
		}
	}
}

?>