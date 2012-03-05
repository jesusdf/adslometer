#!/usr/bin/php -q
<?php

/*

adslometer v0.1.1

dataview.php Data log viewer

Copyright (C) 2010 Jesús Diéguez Fernández

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or see http://www.gnu.org/licenses/.

*/

// Variables generales de configuración
include_once("config.php");

/****************************************************************************************************************/

	$db = mysql_connect(MYSQL_HOST . ":" . MYSQL_PORT, MYSQL_USERNAME, MYSQL_PASSWORD);
	if (!$db)  {
		echo('No se pudieron salvar los datos: ' . mysql_error());
		return null;
	}

	mysql_select_db(MYSQL_DATABASE, $db);

	$sql = "SELECT * FROM velocidad WHERE fecha BETWEEN DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -2 DAY), '%Y-%m-%d') AND DATE_FORMAT(CURDATE(), '%Y-%m-%d 23:59:59')";
	$result = mysql_query($sql);
	if(!$result) {
		echo('No se pudieron leer los datos: ' . mysql_error());
		return null;
	}

	$fuente="Verdana";
	$tamanofuente="1";
	$numcols=mysql_num_fields($result);
	$buffer = "<font face=\"$fuente\" size=\"$tamanofuente\"><table border=\"1\" cellspacing=\"3\" cellpadding=\"3\" frame=\"border\" rules=\"void\">\r\n";
	$buffer .= "<tr><td><font face=\"$fuente\" size=\"$tamanofuente\">Fecha</font></td><td><font face=\"$fuente\" size=\"$tamanofuente\">Run</font></td><td><font face=\"$fuente\" size=\"$tamanofuente\">Modo ADSL</font></td><td><font face=\"$fuente\" size=\"$tamanofuente\">Descarga</font></td><td><font face=\"$fuente\" size=\"$tamanofuente\">&nbsp;Subida&nbsp;</font></td><td><font face=\"$fuente\" size=\"$tamanofuente\">Attn Bajada</font></td><td><font face=\"$fuente\" size=\"$tamanofuente\">SNR Bajada</font></td><td><font face=\"$fuente\" size=\"$tamanofuente\">Attn Subida</font></td><td><font face=\"$fuente\" size=\"$tamanofuente\">SNR Subida</font></td></tr>\r\n";
	while($thisrow=mysql_fetch_row($result)) {
		$buffer .= "<tr>";
		for($i=0;$i<$numcols;$i++) {
			$bgcolor="#FFFFFF";
			$forecolor="#000000";
			if((int)$thisrow[1]===0) {
				// Desconectado
				$bgcolor="FF8040";
			} else {
				if((int)$thisrow[3] < ADSL_MIN_SPEED) {
					// Lento
					$bgcolor="#FFE400";
				} else {
					if ((int)$thisrow[3] < ADSL_MEDIUM_SPEED){
						// Aceptable
						$bgcolor="#C0FF00";
					} else {
						if ((int)$thisrow[3] < ADSL_MAX_SPEED){
							// Cojonudo
							$bgcolor="#40FF00";
						} else {
							// De puta madre
							$bgcolor="#C6D6FC";
						}
					}
				}
			}
			if($i==6) {
				if((int)$thisrow[$i] < MIN_SNR) {
					// SNR chungo :(
					$forecolor="#FF0000";
				}
			}
			$buffer .= "<td align=\"center\" bgcolor=\"$bgcolor\"><font face=\"$fuente\" size=\"$tamanofuente\" color=\"$forecolor\">$thisrow[$i]</td></font>";
		}
		$buffer .= "</tr>\r\n";
	}
	$buffer .= "</table></font>\r\n";

	mysql_close($db);

	echo $buffer;

?>
