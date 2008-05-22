<?php
/*    
	This file is part of STFC.
	Copyright 2006-2007 by Michael Krauss (info@stfc2.de) and Tobias Gafner
		
	STFC is based on STGC,
	Copyright 2003-2007 by Florian Brede (florian_brede@hotmail.com) and Philipp Schmidt
	
    STFC is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    STFC is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


// include global definitions + functions + game-class + player-class
include_once('include/global.php');
include_once('include/sql.php');
include_once('include/functions.php');
error_reporting(E_ALL);


// create sql-object for db-connection
$db = new sql($config['server'].":".$config['port'], $config['game_database'], $config['user'], $config['password']); // create sql-object for db-connection
if ($_GET['size']<1) $_GET['size']=1;
if ($_GET['size']>8) $_GET['size']=8;

$size=$_GET['size'];


$sql = 'SELECT user_id FROM user WHERE user_name LIKE "'.addslashes($_GET['user']).'"';
$user = $db->queryrow($sql);
  if(!isset($user['user_id'])) {
  	
		/*	header("Content-type: image/png");
			header("Pragma: no-cache");
			imagepng($im);*/
			exit;
       }
       
       
       
//Namen zuweisen:
$image_url='maps/tmp/u_'.$user['user_id'].'_'.$size.'.png';
$map_url='maps/tmp/u_'.$user['user_id'].'_'.$size.'.html';
       



//Altes Bild l�schen
if (filemtime($image_url)<time()-3600*6)
{
@unlink($image_url);
@unlink($map_url);
}




// Neues Bild generieren?: 
if (($handle = @fopen ($image_url, "rb"))!=true)
{
	
$map_data='<map name="detail_map">';
	
$im = imagecreatetruecolor(162*$size, 162*$size);
imagecolorallocatealpha($im, 0, 0, 0,0);
$color[1]=imagecolorallocatealpha($im, 90, 64, 64,0);
$color[2]=imagecolorallocatealpha($im, 128, 64, 64,0);
$color[3]=imagecolorallocatealpha($im, 196, 64, 64,0);
$color[4]=imagecolorallocatealpha($im, 96, 96, 96,0);
$color[5]=imagecolorallocatealpha($im, 255, 0, 0,20);

$grid=imagecolorallocatealpha($im, 64, 64, 64,0);
$grid2=imagecolorallocatealpha($im, 128, 128, 128,0);

if ($size>2)
{
for ($t=0; $t<=162;$t++)
{
imageline($im,0,$t*$size,162*$size,$t*$size,$grid);
imageline($im,$t*$size,0,$t*$size,162*$size,$grid);
}
}

imageline($im,0,82*$size,162*$size,82*$size,$grid2);
imageline($im,82*$size,0,82*$size,162*$size,$grid2);


	 
$sql = '
SELECT s.system_id, s.system_name, s.sector_id, s.system_x, s.system_y
 FROM starsystems s';
  if(!$q_systems = $db->query($sql)) {

            message(DATABASE_ERROR, 'Could not query starsystems data');

        }
while($system = $db->fetchrow($q_systems))
$glob_systems[$system['system_id']]=$system;



$q_planets = $db->query('SELECT system_id FROM planets WHERE planet_owner='.$user['user_id'].' GROUP BY system_id');

while($planet = $db->fetchrow($q_planets)) {
$system=$glob_systems[$planet['system_id']];
// Quadrantenkoordinaten
$system['sector_id']--;

$px_x=82*$size;
$px_y=82*$size;
$tmp=$system['sector_id']-243;

if ($system['sector_id']<243)
{
$px_x=0;
$px_y=82*$size;
$tmp=$system['sector_id']-162;
}

if ($system['sector_id']<162)
{
$px_x=82*$size;
$px_y=0;
$tmp=$system['sector_id']-81;
}


if ($system['sector_id']<81)
{
$px_x=0;
$px_y=0;
$tmp=$system['sector_id']-0;
}


// Sektorkoordinaten
$pos_x=0;
$pos_y=0;

while ($tmp>=9)
{
	$tmp-=9;
	$pos_y++;
};
$pos_x=$tmp;


$px_x+=$pos_x*$size*9;
$px_y+=$pos_y*$size*9;


// Systemkoordinaten
$px_x+=($system['system_x'] - 1)*$size+1;
$px_y+=($system['system_y'] - 1)*$size+1;

if ($size>2)	
{	
imagefilledrectangle ($im, $px_x,$px_y, $px_x+$size-2, $px_y+$size-2, $color[5]);
$map_data.='<area href="/game/index.php?a=tactical_cartography&system_id='.encode_system_id($system['system_id']).'" target=_mapshow shape="rect" coords="'.$px_x.','.$px_y.', '.($px_x+$size-2).', '.($px_y+$size-2).'" title="'.$system['system_name'].'">
';

}
else
{
imagefilledrectangle ($im, $px_x-1,$px_y-1, $px_x+$size-2, $px_y+$size-2, $color[5]);
$map_data.='<area href="/game/index.php?a=tactical_cartography&system_id='.encode_system_id($system['system_id']).'" target=_mapshow shape="rect" coords="'.($px_x-1).','.($px_y-1).', '.($px_x+$size-2).', '.($px_y+$size-2).'" title="'.$system['system_name'].'">
';
}
};


if ($size<3) $size2=1;
if ($size==3) $size2=2;
else $size2=$size-2;

if ($size>1)
{
imagestring ($im, $size2,15,162*$size-12-$size,'Erstellt am '.gmdate('d.m.y H:i', (time() +TIME_OFFSET)), $color[4]);
}
else
{
imagestring ($im, $size2,5,162*$size-15,'Erstellt am', $color[4]);
imagestring ($im, $size2,5,162*$size-8,gmdate('d.m.y H:i', (time() +TIME_OFFSET)), $color[4]);
}


$map_data.='</map>';

imagepng($im,$image_url);

if (($handle = @fopen ($map_url, "wt")))
{
	fwrite($handle,$map_data);	
}
fclose($handle);

}





if (!isset($_GET['map']))
{

header("Content-type: image/png");
header("Pragma: no-cache");

if (($handle = @fopen ($image_url, "rb"))!=true) echo 'Lost image!';
else
{
$img = fread($handle, filesize($image_url));
fclose($handle);
}
// Print image to web browser.
print $img;

}
else
{
if (!isset($map_data))
{

if (($handle = @fopen ($map_url, "rt"))!=true) {}
else
{
$map_data = fread($handle, filesize($map_url));
fclose($handle);
}

}
	
echo'<html><body bgcolor="#000000" text="#DDDDDD"  background="|game_url|/gfx/bg_stars1.gif"><center>
<span style="font-family: Verdana; font-size: 15px;"><b>Galaxiekarte des Spielers '.$_GET['user'].':</b></span><br>
<img border=0 usemap="#detail_map" src="'.$image_url.'" >

'.$map_data.'

</body></html>';

	
	
}

$db->close();

?>
