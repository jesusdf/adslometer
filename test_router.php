#!/usr/bin/php -q
<?php

/*

adslometer v0.1.1

test_router.php Router plugin test script

Copyright (C) 2010 Jesús Diéguez Fernández

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or see http://www.gnu.org/licenses/.

*/

// Variables generales de configuración
include_once("config.php");
include_once("functions.php");

/****************************************************************************************************************/

// Intentamos obtener los datos del router que esté configurado y devolvemos su valor
// muy útil para desarrollar nuevos plugins. :)

GetData($estado, $link, $down, $up, $upsnr, $downsnr, $upattn, $downattn);

if (estado=="1") {
	$buffer  = "ADSL: CONNECTED\r\n";
} else {
	$buffer  = "ADSL: DISCONNECTED\r\n";
}
$buffer .= "Mode: $link\r\n";
$buffer .= "Download Rate: $down Kbps\r\n";
$buffer .= "Upload Rate: $up Kbps\r\n";
$buffer .= "Down SNR: $downsnr\r\n";
$buffer .= "Down ATTN: $downattn\r\n";
$buffer .= "Up SNR: $upsnr\r\n";
$buffer .= "Up ATTN: $upattn\r\n";
echo $buffer;

?>
