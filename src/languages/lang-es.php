<?php
/**
 * English PHPMailer language Strings
 * @author Juan David Rueda <jdruedaq@gmail.com>
 */

// SMTP
const alreadyConnected = "Ya está conectado al servidor";
const connectionOpeningTo = "Conexión: abriendo conexión hacia:";
const eDebugError = "Conexión: stream_socket_client no disponible, regresando hacia fsockopen";
const serverConError = "Fallo conectando al servidor";
const connectionOpened = "Conexión: abierta";
const noAllowedAuthBeforeHELO = "Autenticación no habilitada despues de HELO/EHLO";
const noAllowedAuthInThisStage = "La autenticación no está habilitada en esta etapa";
const authMethodRequested = "Método de autenticación solicitado:";
const UNSPECIFIED = "No Especificado";
const authAvailableInServer = "Métodos de autenticación habilitados en el servidor:";
const authRequestNoAvailable = "Método de autenticación solicitado no disponible:";
const authMethodsNotFound = "No se encontraron métodos de autenticación compatibles";
const authMethodSelected = "Método de autenticación seleccionado:";
const requestedAuthMethod = "El método de autenticación solicitado \"%s\" no es soportado por el servidor";
const authMethodNotSupported = "Método de autenticación \"%s\" no está soportado";
const EOFNotice = "NOTICIA SMTP: EOF bloqueado al verificar si está conectado";
const conClosed = "Conexión: cerrada";
const sendWithoutConnect = "Llamado a  %s sin estar conectado";
const commandWithLineBreaks = "Comando '%s' contiene saltos de linea";
const commandFail = "%s comando fallido";
const SMTP_TURNError = "El SMTP TURN comando no implementado";
const noHELOSend = "HELO/EHLO no fue enviado";
const HELONoExtensions = "HELO apretón de manos fue usado; No hay información acerca de extenciones del servidor disponibles";
const conFail = "Conexión Fallida.";

//POP3
const failServerConnect = "Falló conectado al servidor %s en el puerto %s. errno: %s; errstr: %s";
const POP3ConFail = "No conectado a servidor POP3";
const serverErrorInfo = "Servidor reporta un error: %s";
const POP3Warning = "Conectando a POP3 el servidor envión una advertencia PHP:";
