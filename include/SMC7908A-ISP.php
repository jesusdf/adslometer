<?php

/*

adslometer.php v0.1

SMC7908A-ISP.php Ya.com SMC7908A-ISP Configuration File

Copyright (C) 2009 Jesús Diéguez Fernández

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or see http://www.gnu.org/licenses/.

*/

/****************************************************************************************************************/

/*
 * ADSL_MODE: Modo 3, ADSL Normal G.DMT; modo 9 ADSL2+
 */
define("ADSL_MODE", 3);

// Esta función lee un parámetro del router. Estan guardados en la página como variables de JavaScript.
function ParseVar($buffer, $myvar){
	$res=substr(strstr($buffer,"var $myvar="), strlen("var " . $myvar . "="));
	$res=substr($res,0,strpos($res,";"));
	return str_replace('"','',$res);
}

// Esta función parsea los datos de SNR y atenuación. Esta bastante hardcodeado porque la página no tiene una forma sencilla de obtener estos valores.
function Parsedb($buffer, &$upsnr, &$downsnr, &$upattn, &$downattn) {
        $pos=strpos($buffer, "<a NAME=\"defect_i\">");
        $ancla="<td class=tdText width=200>";
        $fin="&nbsp;";
        $pos=strpos($buffer, $ancla, $pos) + strlen($ancla);
        $upsnr=substr($buffer, $pos, strpos($buffer, $fin, $pos) - $pos);
        $pos=strpos($buffer, $ancla, $pos) + strlen($ancla);
        $downsnr=substr($buffer, $pos, strpos($buffer, $fin, $pos) - $pos);
        $pos=strpos($buffer, $ancla, $pos) + strlen($ancla);
        $upattn=substr($buffer, $pos, strpos($buffer, $fin, $pos) - $pos);
        $pos=strpos($buffer, $ancla, $pos) + strlen($ancla);
        $downattn=substr($buffer, $pos, strpos($buffer, $fin, $pos) - $pos);
}

// Esta función se autentifica en el Router
function LoginRouter(){

	$post_data=strlen("pws=" . ROUTER_PASSWORD);

	// Hago login en el router
	$login=GetPage(ROUTER_IP, ROUTER_PORT, "/cgi-bin/login.exe", "POST", "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: $post_data\r\n\r\npws=" . ROUTER_PASSWORD);

	return $login;
	
}

// Esta función cierra la sesión en el Router
function LogoutRouter() {
	
	// Deslogueo
	$login=GetPage(ROUTER_IP, ROUTER_PORT, "/cgi-bin/logout.exe", "GET", null);
	
}

// Esta función devuelve todos los datos de este router
function GetData(&$estado, &$link, &$down, &$up, &$upsnr, &$downsnr, &$upattn, &$downattn) {

	LoginRouter();

	// Cojo el contenido de la pagina de status
	$router_status=GetPage(ROUTER_IP, ROUTER_PORT, "/status_main.stm", "GET", null);
	$adsl_status=GetPage(ROUTER_IP, ROUTER_PORT, "/adsl_status.stm", "GET", null);

	$estado=ParseVar($router_status, "bWanConnected");
	if($estado == "1") {
		$link=ParseVar($router_status, "adsl_mode");
		$down=ParseVar($router_status, "download_rate");
		$up=ParseVar($router_status, "upload_rate");
		Parsedb($adsl_status, $upsnr, $downsnr, $upattn, $downattn);
	}
	
	LogoutRouter();
	
}

function ResyncSpeed() {
	$login=GetPage(ROUTER_IP, ROUTER_PORT, "/cgi-bin/aadsl.exe", "POST", "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: 45\r\n\r\nhiddenAdsl=0&OPER=" . ADSL_MODE . "&savesetting=SAVE+SETTINGS");
}

?>
