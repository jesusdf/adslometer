#!/usr/bin/php -q
<?php

/*

adslometer v0.1.1

test_sms.php SMS test script

Copyright (C) 2010 Jesús Diéguez Fernández

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or see http://www.gnu.org/licenses/.

*/

// Variables generales de configuración
include_once("config.php");
include_once("functions.php");

/****************************************************************************************************************/

// Intentamos enviar un SMS de aviso

echo SendSMS(SMS_NUMBER);

?>
