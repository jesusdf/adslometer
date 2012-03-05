-- adslometer v0.1.1
--
-- a_velocidad.sql Database SQL creation script
-- 
-- Copyright (C) 2010 Jesús Diéguez Fernández
-- 
-- This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
-- 
-- This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or see http://www.gnu.org/licenses/.
-- 
-- 

-- 
-- Creación de la base de datos
-- 

CREATE DATABASE `adsl` DEFAULT CHARACTER SET latin1 COLLATE latin1_spanish_ci;
GRANT ALL ON adsl.* TO adsl@localhost IDENTIFIED BY 'admin';
FLUSH PRIVILEGES;
USE adsl;

--
-- Estructura de tabla para la tabla `a_velocidad`
--

CREATE TABLE IF NOT EXISTS `a_velocidad` (
  `fecha` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `conectado` int(11) NOT NULL default '0',
  `mode` varchar(50) default NULL,
  `downspeed` int(11) default NULL,
  `upspeed` int(11) default NULL,
  `downattn` decimal(5,2) default NULL,
  `downsnr` decimal(5,2) default NULL,
  `upattn` decimal(5,2) default NULL,
  `upsnr` decimal(5,2) default NULL,
  PRIMARY KEY  (`fecha`),
  KEY `downspeed` (`downspeed`,`upspeed`)
);

--
-- Indices
--
CREATE UNIQUE INDEX ix_a_velocidad01 ON a_velocidad(fecha);
CREATE INDEX ix_a_velocidad02 ON a_velocidad(downspeed);
CREATE INDEX ix_a_velocidad03 ON a_velocidad(upspeed);
CREATE INDEX ix_a_velocidad04 ON a_velocidad(downattn);
CREATE INDEX ix_a_velocidad05 ON a_velocidad(downsnr);
CREATE INDEX ix_a_velocidad06 ON a_velocidad(upattn);
CREATE INDEX ix_a_velocidad07 ON a_velocidad(upsnr);

--
-- Creo la vista de esta tabla con los datos netos
--

CREATE VIEW velocidad AS select min(a_velocidad.fecha) AS fecha,a_velocidad.conectado AS conectado,a_velocidad.mode,a_velocidad.downspeed AS downspeed,a_velocidad.upspeed AS upspeed,a_velocidad.downattn AS downattn,a_velocidad.downsnr AS downsnr,a_velocidad.upattn AS upattn,a_velocidad.upsnr AS upsnr from a_velocidad group by date_format(a_velocidad.fecha,_utf8'%Y-%m-%d 00:00:00'),a_velocidad.conectado,a_velocidad.mode,a_velocidad.downspeed,a_velocidad.upspeed,a_velocidad.downattn,a_velocidad.downsnr,a_velocidad.upattn,a_velocidad.upsnr order by min(a_velocidad.fecha) desc;


