<?php

/*

adslometer v0.1.1

config.php Global configuration file

Copyright (C) 2010 Jesús Diéguez Fernández

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or see http://www.gnu.org/licenses/.

*/

/****************************************************************************************************************/

/*
 * No mostrar los errores de ejecución, comentar esta linea para depurar los sripts
 */

error_reporting(E_NONE);

/*
 * Configuración relativa al Router
 */
define("ROUTER_IP", "192.168.1.1");
define("ROUTER_PORT", 80);
define("ROUTER_USERNAME", "");
define("ROUTER_PASSWORD", "admin");
define("ROUTER_STATS", "/tmp/router.txt");

/*
 * Tipo de Router
 */
define("ROUTER_MODEL", "SMC7908A-ISP"); // Router de Ya.com con soporte para VOIP 
#define("ROUTER_MODEL", "Zyxel_660HW"); // Router de Telefónicapara ADSL normal
#define("ROUTER_MODEL", "Conceptronic_C54APRA2+"); // Router de Conceptronic con soporte para ADSL2+

/* Los archivos de configuración de cada router deben implementar las siguientes funciones:
 * GetData(&$estado, &$link, &$down, &$up, &$upsnr, &$downsnr, &$upattn, &$downattn)
 * ResyncSpeed()
 */
include_once("include/" . ROUTER_MODEL . ".php");

/*
 * Configuración relativa a la capacidad de nuestro ADSL.:
 * ADSL_MIN_SPEED: Velocidad mínima que se quisiera alcanzar para navegar. P.Ej.- En mi ADSL de 1 Mb el valor es 600 Kbps.
 * ADSL_MEDIUM_SPEED: Velocidad aceptable que se quisiera alcanzar. P.Ej.- En mi ADSL de 1 Mb el valor es 900 Kbps.
 * ADSL_MAX_SPEED: Velocidad cercana al máximo que da nuestra línea. P.Ej.-n mi ADSL de 1 Mb el valor es 1400 Kbps (El límite máximo de mi línea son 2048 Kbps).
 */

define("ADSL_MIN_SPEED", 6000);
define("ADSL_MEDIUM_SPEED", 10000);
define("ADSL_MAX_SPEED", 14000);

define("MIN_SNR", 8);

/*
 * Configuración relativa al servidor MYSQL donde se guardarán los datos
 */
define("MYSQL_HOST", "localhost");
define("MYSQL_PORT", "3306");
define("MYSQL_DATABASE", "adsl");
define("MYSQL_USERNAME", "adsl");
define("MYSQL_PASSWORD", "admin");

/*
 * Configuración relativa a los parámetros del gráfico que será generado:
 * PNG_OUTPUT: Ruta al fichero donde se generará la imagen PNG
 * GRAPH_MAXSPEED: Máxima velocidad teórica de nuestra conexión
 * GRAPH_SPEED_STEP: Indica cada cuantos Kbps se pondrán las marcas en el eje Y (Velocidad)
 * GRAPH_DATA y GRAPH_SETUP: Ruta a dos ficheros temporales que se crearán para poder generar el gráfico
 * SECONDS_BEFORE: Tiempo en segundos anterior a la fecha/hora actual desde la que se empezarán a pintar los datos del gráfico. Por defecto 21600, 6 horas.
 */
define("PNG_OUTPUT", "/var/www/adsl.png");
define("GRAPH_MAXSPEED", 16000);
define("GRAPH_SPEED_STEP", 4000);
define("GRAPH_DATA", "/tmp/adsl.dat");
define("GRAPH_SETUP", "/tmp/adsl.graph");
define("SECONDS_BEFORE", 21600);

/*
 * Número de notificación por SMS
 * Sólo funciona si tenemos un equipo conectado a otra red distinta con internet
 * El puerto es de un proxy HTTP sin autenticación
 * Lo que hace es usar un servicio beta de google que ha sido cerrado para enviar un sms.
 * Aunque el mensaje en sí no contiene informacion, si recibes un mensaje de google sabrás que la línea ha perdido conectividad.
 */
define("SMS_SEND", 0);
define("SMS_NUMBER", "666123456");
define("SMS_GATEWAY", "192.168.1.250");
define("SMS_GATEWAY_PORT", "8080");

?>
