#!/usr/bin/php -q
<?php

/*

adslometer v0.1.1

adslometer.php Main script file

Copyright (C) 2010 Jesús Diéguez Fernández

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or see http://www.gnu.org/licenses/.

*/

// Variables generales de configuración
include_once("config.php");

// Funciones comunes
include_once("functions.php");

/****************************************************************************************************************/

// Esta función realiza todo el trabajo
function CheckSpeed($again) {

	// Se pide al php específico que nos rellene los datos
	GetData($estado, $link, $down, $up, $upsnr, $downsnr, $upattn, $downattn);

	// Proceso los datos que tengo
	if($estado == "1") {	
		$buffer  = "ADSL: CONNECTED\r\n";
		$buffer .= "Mode: $link\r\n";
   	$buffer .= "Download Rate: $down Kbps\r\n";
		$buffer .= "Upload Rate: $up Kbps\r\n";
		echo $buffer;
		
		WriteCache(ROUTER_STATS, $buffer);
		LogData($estado, $down, $up, $link, $upsnr, $downsnr, $upattn, $downattn);
		GraphData();
	
		// Normas para intentar recuperar la velocidad perdida:
		// ---Que la velocidad sea inferior a ADSL_MIN_SPEED
		// Que la velocidad sea inferior a ADSL_MEDIUM_SPEED y sean las 9 de la mañana (es de las mejores horas para sincronizar rápido). En ocasiones sin razón aparente se puede alzancar más velocidad aunque los datos de SNR y atenuación digan lo contrario
		// Que la velocidad sea inferior a ADSL_MEDIUM_SPEED y el SNR sea igual o superior a 13
		// Que la velocidad sea inferior a ADSL_MAX_SPEED y el SNR sea igual o superior a 14
		if(((int)$down < ADSL_MEDIUM_SPEED && date('H:i')=='09:00') || ((int)$down < ADSL_MEDIUM_SPEED && (int)$downsnr >= 13) || (int)$down >= ADSL_MEDIUM_SPEED && (int)$downsnr >= 14) {
			/*
			if($link=="G.992.1 (G.DMT)") {
				// Si ya estaba como ADSL normal, probamos con ADSL2+
				// Comentado porque tras probarlo no aporta ninguna mejora añadida
				$adsl_mode=9;
			} else {
				$adsl_mode=3;
			}
			*/
			if($down < ADSL_MIN_SPEED) {
				echo "Download speed is too low, forcing resync...\r\n";
			}
			ResyncSpeed();
			$again++;
		} else {
			$again=0;
		}
	} else {
		echo "ADSL is disconnected\r\n";
		LogData($estado, '0', '0', "", '0.0', '0.0', '0.0', '0.0');
		SendSMS(SMS_NUMBER);
	}

	/*
	if($again > 0 && $again < 5) {
		// Modo agresivo, forzamos que resincronice hasta 4 veces, en intervalos de 60 segundos, asi se enlazará con la ejecución del siguiente script
		// NO RECOMENDABLE: Deja momentáneamente sin conexión durante algunos minutos. Es preferible una conexión más lenta pero sin cortes.
		sleep(60);
		CheckSpeed($again);
	}
	*/

}

CheckSpeed(0);

?>

