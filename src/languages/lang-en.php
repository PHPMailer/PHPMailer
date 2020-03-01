<?php
/**
 * English PHPMailer language Strings
 * @author Juan David Rueda <jdruedaq@gmail.com>
 */

// SMTP
const alreadyConnected = "Already connected to a server";
const connectionOpeningTo = "Connection: opening to";
const eDebugError = "Connection: stream_socket_client not available, falling back to fsockopen";
const serverConError = "Failed to connect to server";
const connectionOpened = "Connection: opened";
const noAllowedAuthBeforeHELO = "Authentication is not allowed before HELO/EHLO";
const noAllowedAuthInThisStage = "Authentication is not allowed at this stage";
const authMethodRequested = "Auth method requested:";
const UNSPECIFIED = "UNSPECIFIED";
const authAvailableInServer = "Auth methods available on the server:";
const authRequestNoAvailable = "Requested auth method not available:";
const authMethodsNotFound = "No supported authentication methods found";
const authMethodSelected = "Auth method selected:";
const requestedAuthMethod = "The requested authentication method \"%s\" is not supported by the server";
const authMethodNotSupported = "Authentication method \"%s\" is not supported";
const EOFNotice = "SMTP NOTICE: EOF caught while checking if connected";
const conClosed = "Connection: closed";
const sendWithoutConnect = "Called %s without being connected";
const commandWithLineBreaks = "Command '%s' contained line breaks";
const commandFail = "%s command failed";
const SMTP_TURNError = "The SMTP TURN command is not implemented";
const noHELOSend = "No HELO/EHLO was sent";
const HELONoExtensions = "HELO handshake was used; No information about server extensions available";
const conFail = "Connection failed.";

//POP3
const failServerConnect = "Failed to connect to server %s on port %s. errno: %s; errstr: %s";
const POP3ConFail = "Not connected to POP3 server";
const serverErrorInfo = "Server reported an error: %s";
const POP3Warning = "Connecting to the POP3 server raised a PHP warning:";
