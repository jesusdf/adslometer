<?php

/*

adslometer.php v0.1.1

Conceptronic_C54APRA2+.php Conceptronic C54APRA2+ Configuration File

Copyright (C) 2010 Jesús Diéguez Fernández

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or see http://www.gnu.org/licenses/.

*/

/****************************************************************************************************************/

// Este router basa su autentificación web en una autentificación clásica.
// Almacenamos el usuario y contraseña codificados en esta cookie para posteriormente hacer las peticiones oportunas.
$login_cookie="";

/*
 * ADSL_MODE: Modo 12, ADSL Normal G.DMT; modo 16 ADSL2+
 */
define("ADSL_MODE", 12);

/*
<option value='0'>Not Trained</option>
<option value='1'>ADSL2+ (Multi-Mode)</option>
<option value='2'>ADSL2+ (ITU G.992.5)</option>
<option value='3'>ADSL2+ (ITU G.992.5) with DELT</option>
<option value='4'>ADSL2+ (Multi-Mode except T1.413)</option>
<option value='5'>ADSL2 (Multi-Mode)</option>
<option value='6'>ADSL2 (ITU G.992.3)</option>
<option value='7'>ADSL2 (ITU G.992.3) with DELT</option>
<option value='8'>RE-ADSL</option>
<option value='9'>RE-ADSL with DELT</option>
<option value='10'>ADSL (Multi-Mode)</option>
<option value='11'>ADSL (ITU G.992.1)</option>
<option value='12'>ADSL (ITU G.992.2)</option>
<option value='13'>ADSL (ANSI T1.413)</option>
<option value='14'>ADSL2+ (Multi-Mode except Annex M)</option>
<option value='15'>ADSL2 (Annex M)</option>
<option value='16'>ADSL2+ (Annex M)</option>
*/

// Esta función lee un parámetro del router. Estan guardados en la página como variables de JavaScript.
function ParseVar($buffer, $myvar){
	$res=substr(strstr($buffer,"<td nowrap class='tabdata' colspan=2>$myvar</td>"), strlen("<td nowrap class='tabdata' colspan=2>$myvar</td>"));
	$res=substr(strstr($res, "<td nowrap class='tabdata' colspan=2>"), strlen("<td nowrap class='tabdata' colspan=2>"));
	$res=substr($res, 0, strpos($res, "</td>"));
	return str_replace(' ','',$res);
}

// Funcion de ayuda para reducir el código de la funcion Parsedb
function ParseValue($buffer, $value, &$retval1, &$retval2) {
	$tmp=strstr($buffer,"<TD class=tabdata noWrap width=\"25%\"><div align='left'>$value</div></TD");
	$tmp=substr($tmp, strlen("<TD class=tabdata noWrap width=\"25%\"><div align='left'>$value</div></TD"));
        $retval1=strstr($tmp, "<TD class=tabdata noWrap width=\"25%\"><div align='left'>");
        $retval1=substr($retval1, strlen("<TD class=tabdata noWrap width=\"25%\"><div align='left'>"));
	$retval2=strstr($retval1, "<TD class=tabdata noWrap width=\"25%\"><div align='left'>");
	$retval2=substr($retval2, strlen("<TD class=tabdata noWrap width=\"25%\"><div align='left'>"));
	$retval1=substr($retval1, 0, strpos($retval1, "</div>"));
        $retval2=substr($retval2, 0, strpos($retval2, "</div>"));
}

// Esta función parsea los datos de SNR y atenuación. Esta bastante hardcodeado porque la página no tiene una forma sencilla de obtener estos valores.
function Parsedb($buffer, &$down, &$up, &$upsnr, &$downsnr, &$upattn, &$downattn) {
	ParseValue($buffer, "SNR Margin", $downsnr, $upsnr);
	ParseValue($buffer, "Line Attenuation", $downattn, $upattn);
	ParseValue($buffer, "Data Rate", $down, $up);
}

// Esta función se autentifica en el Router
function LoginRouter(){

	global $login_cookie;

	// Construyo la cadena codificada en base64 para autentificar el usuario y contraseña
	$login_cookie="Authorization: Basic " . base64_encode(ROUTER_USERNAME . ":" . ROUTER_PASSWORD);

	// Hago login en el router dos veces si es necesario ya que algunos routers ignoran la primera vez
	$login=GetPage(ROUTER_IP, ROUTER_PORT, "/", "GET", $login_cookie . "\r\n\r\n");
	if (is_null($login) == false && (substr($login, 9, 3) == "401")) {
		$login="";
		
		// Nos pide la autentificación, tras esperar un segundo, se la damos
		$login=GetPage(ROUTER_IP, ROUTER_PORT, "/", "GET", $login_cookie . "\r\n\r\n");
	}
	return $login;
}
	
// Esta función cierra la sesión en el Router
function LogoutRouter() {

	global $login_cookie;	
	
	// Deslogueo
	$logout=GetPage(ROUTER_IP, ROUTER_PORT, "/html/closeWin.html", "GET", $login_cookie . "\r\n\r\n");
	
	return $logout;
	
}

// Esta función devuelve todos los datos de este router
function GetData(&$estado, &$link, &$down, &$up, &$upsnr, &$downsnr, &$upattn, &$downattn) {

	global $login_cookie;
	
	LoginRouter();
	
	// Cojo el contenido de la pagina de status
	$router_status=GetPage(ROUTER_IP, ROUTER_PORT, "/cgi-bin/webcm?getpage=../html/status/status_adsl.htm", "GET", $login_cookie . "\r\n\r\n");

	$estado=ParseVar($router_status, "Line State");
	if($estado == "Connected") {
		$estado="1";
		$link=ParseVar($router_status, "Modulation");
		Parsedb($router_status, $down, $up, $upsnr, $downsnr, $upattn, $downattn);
	} else {
		$estado="0";
		$link=ParseVar($router_status, "Modulation");
                Parsedb($router_status, $down, $up, $upsnr, $downsnr, $upattn, $downattn);
	}
	
	LogoutRouter();
	
}

function ResyncSpeed() {

	global $login_cookie;

	$reset=GetPage(ROUTER_IP, ROUTER_PORT, "/cgi-bin/webcm", "POST", $login_cookie . "\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: 76\r\n\r\ngetpage=..%2Fhtml%2Fadvanced%2Fadv_adsl.htm&sar%3Asettings%2Fmodulation=" . ADSL_MODE . "+");

}

?>
