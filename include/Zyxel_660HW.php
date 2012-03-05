<?php

/*

adslometer.php v0.1.1

Zyxel_660HW.php Telefonica Zyxel 660HW Configuration File

Copyright (C) 2010 Jesús Diéguez Fernández

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or see http://www.gnu.org/licenses/.

*/

/****************************************************************************************************************/

// Este router basa su autentificación web en una autentificación clásica.
// Almacenamos el usuario y contraseña codificados en esta cookie para posteriormente hacer las peticiones oportunas.
$login_cookie="";

// Esta función lee un parámetro del router. Estan guardados en la página como variables de JavaScript.
function ParseVar($buffer, $myvar){
	$res=substr(strstr($buffer,"$myvar:<b>"), strlen($myvar . ":<b>"));
	$res=substr($res,0,strpos($res,"<"));
	$res=str_replace('kbps','',$res);
	$res=str_replace("\t",'',$res);
	return str_replace(' ','',$res);
}

// Esta función parsea los datos de SNR y atenuación. Esta bastante hardcodeado porque la página no tiene una forma sencilla de obtener estos valores.
function Parsedb($bufferdown, $bufferup, &$upsnr, &$downsnr, &$upattn, &$downattn) {
	$downsnr=substr(strstr($bufferdown,"noise margin downstream:"), strlen("noise margin downstream:"));
	$downsnr=substr($downsnr,0,strpos($downsnr, "db"));
	$upsnr=substr(strstr($bufferup,"noise margin upstream:"), strlen("noise margin upstream:"));
	$upsnr=substr($upsnr,0,strpos($upsnr, "db"));
	$downattn=substr(strstr($bufferdown,"attenuation downstream:"), strlen("noise margin downstream:"));
        $downattn=substr($downattn,0,strpos($downattn, "db"));
        $upattn=substr(strstr($bufferup,"attenuation upstream:"), strlen("noise margin upstream:"));
        $upattn=substr($upattn,0,strpos($upattn, "db"));
	$downsnr=str_replace(' ','',$downsnr);
	$downattn=str_replace(' ','',$downattn);
	$upsnr=str_replace(' ','',$upsnr);
	$upattn=str_replace(' ','',$upattn);
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
	$logout=GetPage(ROUTER_IP, ROUTER_PORT, "/Logout.html", "GET", $login_cookie . "\r\n\r\n");
	
	return $logout;
	
}

// Esta función devuelve todos los datos de este router
function GetData(&$estado, &$link, &$down, &$up, &$upsnr, &$downsnr, &$upattn, &$downattn) {

	global $login_cookie;
	
	LoginRouter();
	
	// Cojo el contenido de la pagina de status
	$router_status=GetPage(ROUTER_IP, ROUTER_PORT, "/SysStatistics_ADSL.html", "GET", $login_cookie . "\r\n\r\n");
	$adslstatus_request=GetPage(ROUTER_IP, ROUTER_PORT, "/Forms/DiagADSL_1", "POST", $login_cookie . "\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: 60\r\n\r\nLineInfoDisplay=&DiagDownstreamNoise=Downstream+Noise+Margin", '1.1', 0, "http://" . ROUTER_IP . ":" . ROUTER_PORT . "/DiagADSL.html");
	$adsldown_status=GetPage(ROUTER_IP, ROUTER_PORT, "/DiagADSL.html", "GET", $login_cookie . "\r\n\r\n");
	$adslstatus_request=GetPage(ROUTER_IP, ROUTER_PORT, "/Forms/DiagADSL_1", "POST", $login_cookie . "\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: 58\r\n\r\nLineInfoDisplay=&DiagUpstreamNoise=+Upstream+Noise+Margin+", '1.1', 0, "http://" . ROUTER_IP . ":" . ROUTER_PORT . "/DiagADSL.html");
	$adslup_status=GetPage(ROUTER_IP, ROUTER_PORT, "/DiagADSL.html", "GET", $login_cookie . "\r\n\r\n");

	$estado=ParseVar($router_status, "Link Status");
	if($estado != "Down") {
		$estado="1";
		$link="ADSL";
		$down=ParseVar($router_status, "Downstream Speed");
		$up=ParseVar($router_status, "Upstream Speed");
		Parsedb($adsldown_status, $adslup_status, $upsnr, $downsnr, $upattn, $downattn);
	} else {
		$estado="0";
		$link="DOWN";
		$down=ParseVar($router_status, "Downstream Speed");
                $up=ParseVar($router_status, "Upstream Speed");
		Parsedb($adsldown_status, $adslup_status, $upsnr, $downsnr, $upattn, $downattn);
	}
	
	LogoutRouter();
	
}

function ResyncSpeed() {

	global $login_cookie;

	$reset=GetPage(ROUTER_IP, ROUTER_PORT, "/Forms/DiagADSL_1", "POST", $login_cookie . "\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: 48\r\n\r\nLineInfoDisplay=&DiagResetADSL=Reset+ADSL+Line+", '1.1', 0, "http://" . ROUTER_IP . ":" . ROUTER_PORT . "/DiagADSL.html");

}

?>
