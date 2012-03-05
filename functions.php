<?php

/*

adslometer.php v0.1.1

functions.php Common function library

Copyright (C) 2010 Jesús Diéguez Fernández

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or see http://www.gnu.org/licenses/.

*/

/****************************************************************************************************************/

// Esta función realiza una petición HTTP y devuelve la página completa, incluyendo las cabeceras del servidor
// Se le ha añadido funcionalidad para poder solicitar paginas con autenticacion así como un user agent para poder identificar las peticiones del script
function GetPage($host, $port, $page, $method, $tail=null, $version='1.1', $keepconnection=0, $referer=null) {
	$da = fsockopen($host, $port, $errno, $errstr, $timeout=15);
	if (!$da) {
	    echo "No se pudo conectar al host<br />\n";
	    return null;
	} else {
	    $salida = "$method $page HTTP/$version\r\n";
	    $salida .= "Host: $host:$port\r\n";
	    $salida .= "User-Agent: Mozilla/5.0 (Console; U; adslometer)\r\n";
	    $salida .= "Accept: text/html\r\n";
	    $salida .= "Accept-Encoding: deflate\r\n";	
	    $salida .= "Accept-Charset: ISO-8859-1\r\n";    
	    if($keepconnection==0) {
	    	$salida .= "Connection: close\r\n";
	    } else {
			$salida .= "Keep-Alive: $keepconnection\r\n";
			$salida .= "Connection: keep-alive\r\n";	    
	    }
	    if( is_null($referer)  == false ) {
                        $salida .= "Referer: " . $referer . "\r\n";
            }
	    if( is_null($tail) ) {
			$salida .= "\r\n";
	    } else {
			$salida .= $tail;
	    }
	    fwrite($da, $salida);
	    ReadData($da, $r);
	    fclose($da);
	    return $r;
	}
}

function ReadData(&$fsocket, &$buffer) {
	while (!feof($fsocket)) {
		$buffer .= fgets($fsocket,128);
	}
}

// Esta función escribe el archivo con la información de la velocidad actual del ADSL. Es útil para poder mostarla o utilizarla en otros scripts sin tener que conectarse a la base de datos
function WriteCache($cache_file, $data) {

        $fh = fopen($cache_file, "w");
        fwrite($fh, $data);
        fclose($fh);

}

// Esta función genera un gráfico con los últimos datos
function GraphData() {

	$graph = "# Indicamos el archivo .png de destino\r\n";
	$graph .= "set size 1,0.5\r\n";
	$graph .= "set ytics 0," . GRAPH_SPEED_STEP . "\r\n";
	$graph .= "set ylabel \"Kbps\" 0,0\r\n";
	$graph .= "set terminal png\r\n";
	$graph .= "set output \"" . PNG_OUTPUT . "\"\r\n";
	$graph .= "set datafile separator \",\"";
	$graph .= "# Le indicamos que los datos de las X son tiempos\r\n";
	$graph .= "set xdata time\r\n";
	$graph .= "# Formato de los ticks en el eje X\r\n";
	$graph .= "set format x \"%H:%M\"\r\n";
	$graph .= "# Formato de la hora en el archivo\r\n";
	$graph .= "set timefmt \"%Y-%m-%d %H:%M:%S\"\r\n";
	$graph .= "# Rango de tiempo a mostrar\r\n";
	$graph .= "set xrange [\"" . date("Y-m-d H:i:s", time() - SECONDS_BEFORE) . "\":\"" . date("Y-m-d H:i:s", time()) . "\"]\r\n";
	$graph .= "set yrange [0:" . GRAPH_MAXSPEED . "]";
	$graph .= "# Quitamos la leyenda\r\n";
	$graph .= "set key off\r\n";
	$graph .= "set title \"ADSL Sync Speed\"\r\n";
	$graph .= "plot \"" . GRAPH_DATA . "\" using 1:3 with lines\r\n";

	$db = mysql_connect(MYSQL_HOST . ":" . MYSQL_PORT, MYSQL_USERNAME, MYSQL_PASSWORD);
	
	if (!$db)  {
		echo('No se pudieron salvar los datos: ' . mysql_error());
		return 0;
	}

	mysql_select_db(MYSQL_DATABASE, $db);

	$sql = "SELECT fecha, downspeed, upspeed FROM a_velocidad WHERE fecha >= '" . date("Y-m-d H:i:s", time() - SECONDS_BEFORE) . "' ORDER BY fecha ASC";
	$result = mysql_query($sql);
	if(!$result) {
		echo('No se pudieron leer los datos: ' . mysql_error());
		return 0;
	}

	$fd=fopen(GRAPH_DATA, "w");
	if($fd) {
		
		while($thisrow=mysql_fetch_row($result)) {
			fwrite($fd,$thisrow[0] . "," . $thisrow[1] . "," . $thisrow[1] . "\r\n");
		}
		fclose($fd);
		
		$ftemp=fopen(GRAPH_SETUP,"w");
		if($ftemp) {
			fwrite($ftemp, $graph);
			fclose($ftemp);
			exec("gnuplot " . GRAPH_SETUP);
		}

	}	

	mysql_close($db);

}

// Esta función escribe los datos actuales de la conexión a la base de datos
function LogData($con, $down, $up, $mode, $upsnr, $downsnr, $upattn, $downattn) {

	$db = mysql_connect(MYSQL_HOST . ":" . MYSQL_PORT, MYSQL_USERNAME, MYSQL_PASSWORD);
	if (!$db)  {
	  echo('No se pudieron salvar los datos: ' . mysql_error());
	  return 0;
	}

	mysql_select_db(MYSQL_DATABASE, $db);

	$sql="INSERT INTO a_velocidad (downspeed,upspeed,mode,conectado,upsnr,downsnr,upattn,downattn) VALUES ($down, $up, '$mode', $con, $upsnr, $downsnr, $upattn, $downattn)";
	$result = mysql_query($sql);
	mysql_close($db);

}

function SendSMS($number) {
	if(SMS_SEND != 0) {
		$msg="ADSL";
		$content="ec=on&c=1&text=Envia+SmS&client=mobile&hl=es&gl=ES&mobile_user_id=$number&from=$msg&send_button=Enviar+SMS";
		$nlen=strlen($content);
		/*
		La petición no se puede hacer directamente, tiene que hacerse a través de un proxy que esté en una red con conexión.
		$sms=GetPage("www.google.es", 80, "/sendtophone", "POST", "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: $nlen\r\n\r\n$content");
		*/
		$sms=GetPage(SMS_GATEWAY, SMS_GATEWAY_PORT, "http://www.google.es/sendtophone", "POST", "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: $nlen\r\n\r\n$content");

		return $sms;
	} else {
		return null;
	}

}

?>
