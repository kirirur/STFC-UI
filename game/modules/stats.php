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

include('stats.sprache.php');
    
    $STEMPLATE_MODULES = array(
        'overview' => constant($game->sprache("TEXT1")),
        'member' => constant($game->sprache("TEXT2")),
        'pacts' => constant($game->sprache("TEXT3")),
    );

function GiveThumb($path)
{
 $width=$height=100;

 if (is_file($path)!=1) $path='gallery/no_img.jpg';
 else
 {
 	$size = getimagesize($path);
 	if ($size[0]>$size[1]) {$height = 100 * ($size[1] / $size[0]);}
 	else {$width = 100 * ($size[0] / $size[1]);}
 }


return '<img src="'.$path.'" width="'.$width.'" height="'.$height.'" border=0>';
}


function getimagesize_remote($image_url) {
   if (($handle = @fopen ($image_url, "rb"))!=true) {return;}
   $contents = "";
   $count=0;
   if ($handle) {
   do {
       $count += 1;
       $data = fread($handle, 8192);
       if (strlen($data) == 0) {
           break;
       }
   $contents .= $data;
   } while(true);
   } else { return false; }
   fclose ($handle);

   $im = ImageCreateFromString($contents);
   if (!$im) { return false; }
   $gis[0] = ImageSX($im);
   $gis[1] = ImageSY($im);
// array member 3 is used below to keep with current getimagesize standards
   $gis[3] = "width={$gis[0]} height={$gis[1]}";
   ImageDestroy($im);
   return $gis;
}


$game->init_player();


$rank_honor = array();
$rank_honor[0]=0;
$rank_honor[1]=25;
$rank_honor[2]=50;
$rank_honor[3]=150;
$rank_honor[4]=250;
$rank_honor[5]=400;
$rank_honor[6]=700;
$rank_honor[7]=1200;
$rank_honor[8]=2000;
$rank_honor[9]=5000;


$game->out('<center><span class="caption">'.constant($game->sprache("TEXT4")).'</span></center><br><br>');



function Show_Main()
{
global $db;
global $game;
$game->out('<br><center><table boder=0 cellpadding=0 cellspacing=0 class="style_inner" width=350>
<tr height="30">
<td width=150 align=middle>
<a href="'.parse_link('a=stats&a2=player_ranking').'"><span class="sub_caption2">'.constant($game->sprache("TEXT5")).'</a>
</td>
<td width=150 align=middle>
<a href="'.parse_link('a=stats&a2=alliance_ranking').'"><span class="sub_caption2">'.constant($game->sprache("TEXT6")).'</a>
<br></td>
</tr>
</table><br><br><br>
');

}


function Show_Alliance()
{
if (!isset($_REQUEST['view'])) $_REQUEST['view']='overview';

$_REQUEST['id']=(int)$_REQUEST['id'];

global $db;
global $game;
global $userquery;

global $start;
global $search_name, $STEMPLATE_MODULES;

// Men BASIC
Show_Main();

$query='SELECT * FROM alliance WHERE alliance_id="'.$_REQUEST['id'].'"';
$alliancequery=$db->query($query);
if (($alliance = $db->fetchrow($alliancequery))==false) {$game->out('<center><p><span class="sub_caption">'.constant($game->sprache("TEXT7")).'</span></center>');}
else
{
$game->out('<center><table boder=0 cellpadding=0 cellspacing=0 class="style_outer"><tr><td width=400>
<br><center>');
$game->out('<center><span class="sub_caption">'.constant($game->sprache("TEXT8")).' '.$alliance['alliance_name'].' ['.$alliance['alliance_tag'].']</span></center>');
// Show the alliance logo:
if (!empty($alliance['alliance_logo']))// && fopen($alliance['logo_link'],"r")!=FALSE)
{
$info = getimagesize_remote($alliance['alliance_logo']);
if ($info[0]>0 && $info[1]>0 && $info[0]<=350 && $info[1]<=280)
{
$game->out('<table border=0 cellpadding=0 cellspacing=0><tr><td width=350><center><img src="'.$alliance['alliance_logo'].'"></center></td></tr></table><br>');
}
}
else {$game->out('<br>');}
// End of logo part


// Members & Score:
$membs=$db->queryrow('SELECT COUNT(user_id) AS num FROM user WHERE (user_alliance="'.$alliance['alliance_id'].'") AND (user_active=1)');
$members=$membs['num'];
$game->out('<table boder=0 cellpadding=0 cellspacing=0 class="style_inner"><tr><td width=350><span class="sub_caption"><b><center>'.constant($game->sprache("TEXT9")).'</center></b></span><br>
<center>
<table border=0 cellpadding=1 cellspacing=1>
<tr><td width=100>'.constant($game->sprache("TEXT10")).'</td><td>'.$members.'</td></tr>
<tr><td>'.constant($game->sprache("TEXT11")).'</td><td>'.$alliance['alliance_points'].'</td></tr>');

$homepage_name=$alliance['alliance_homepage'];
if (strlen($alliance['alliance_homepage'])>30)
{
$homepage_name=substr($alliance['alliance_homepage'], 0,27);
$homepage_name=$homepage_name.'...';
}

if (!empty($alliance['alliance_homepage'])) { $game->out('<tr><td>'.constant($game->sprache("TEXT12")).'</td><td><a href="'.$alliance['alliance_homepage'].'" target=_blank><b>'.$homepage_name.'</b></a></td></tr>'); }
if (!empty($alliance['alliance_irc'])) { $game->out('<tr><td>'.constant($game->sprache("TEXT13")).'</td><td><b>'.$alliance['alliance_irc'].'</b></a></td></tr>'); }
$game->out('</table><br>');


$game->out('<center>'.display_view_navigation('stats&a2=viewalliance&id='.$_REQUEST['id'].'', $_REQUEST['view'], $STEMPLATE_MODULES).'</center><br>');







if ($_REQUEST['view']=='overview')
{

$game->out('<br><center><span class="sub_caption2">'.constant($game->sprache("TEXT14")).'</span><br><a href="alliancemap.php?alliance='.$alliance['alliance_tag'].'&size=6&map" target=_blank><img src="alliancemap.php?alliance='.$alliance['alliance_tag'].'&size=1" border=0></a>');

$game->out('<br><center><span class="sub_caption2">'.constant($game->sprache("TEXT15")).'</span>');
// Allytext:
$game->out('
<table boder=0 cellpadding=0 cellspacing=0 width=300 class="style_inner"><tr><td width=13>&nbsp;</td><td width=274><center></u>
'.stripslashes($alliance['alliance_text']).'</center>
</td><td width=13>&nbsp;</td></tr></table><br>
');
}


if ($_REQUEST['view']=='member')
{
$game->out('<br><center><span class="sub_caption2">'.constant($game->sprache("TEXT16")).'</span>');

    $sql = 'SELECT user_id, user_name, user_points, user_planets, user_alliance_status
            FROM user
            WHERE user_alliance = '.$alliance['alliance_id'].' AND user_active=1
            ORDER BY user_points DESC';

    if(!$q_user = $db->query($sql)) {
        message(DATABASE_ERROR, 'Could not query alliance user data');
    }

    if($db->num_rows() == 0) {
        message(NOTICE, constant($game->sprache("TEXT17")));
    }

    $member_status = array(
        ALLIANCE_STATUS_MEMBER => '%s',
        ALLIANCE_STATUS_ADMIN => '<span style="color: #FFFF00; font-weight: bold;">%s</span>',
        ALLIANCE_STATUS_OWNER => '<span style="color: #FF0000; font-weight: bold;">%s</span>',
        ALLIANCE_STATUS_FINANZ => '%s',
        ALLIANCE_STATUS_DIPLO => '<span style="color: #FFA500; font-weight: bold;">%s</span>'
    );

        $game->out('
      <table width="300" align="center" border="0" cellpadding="2" cellspacing="2" class="style_inner">
        <tr>
          <td width="160"><b>'.constant($game->sprache("TEXT18")).'</b></td>
          <td width="70"><b>'.constant($game->sprache("TEXT19")).'</b></td>
          <td width="70"><b>'.constant($game->sprache("TEXT11")).'</b></td>
        </tr>
        ');

        while($user = $db->fetchrow($q_user)) {
            $game->out('
        <tr>
          <td><a href="'.parse_link('a=stats&a2=viewplayer&id='.$user['user_id']).'">'.sprintf($member_status[$user['user_alliance_status']], $user['user_name']).'</a></td>
          <td>'.$user['user_planets'].'</td>
          <td>'.$user['user_points'].'</td>
        </tr>
            ');
        }

        $game->out('</table>');


    $game->out('
<br>
<center>
<i>'.constant($game->sprache("TEXT20")).'</i>&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);">'.constant($game->sprache("TEXT21")).'</a>&nbsp;&nbsp;&nbsp;'.sprintf($member_status[ALLIANCE_STATUS_DIPLO], constant($game->sprache("TEXT22"))).'&nbsp;&nbsp;&nbsp;'.sprintf($member_status[ALLIANCE_STATUS_ADMIN], constant($game->sprache("TEXT23")) ).'&nbsp;&nbsp;&nbsp;'.sprintf($member_status[ALLIANCE_STATUS_OWNER], constant($game->sprache("TEXT24")) ).'
</center><br>
    ');



}


if ($_REQUEST['view']=='pacts')
{
$game->out('<br><center><span class="sub_caption2">'.constant($game->sprache("TEXT25")).'</span>');
    $sql = 'SELECT d.*,
                   a1.alliance_name AS alliance1_name,
                   a2.alliance_name AS alliance2_name
            FROM (alliance_diplomacy d)
            INNER JOIN (alliance a1) ON a1.alliance_id = d.alliance1_id
            INNER JOIN (alliance a2) ON a2.alliance_id = d.alliance2_id
            WHERE ((d.alliance1_id = '.$alliance['alliance_id'].') OR (d.alliance2_id = '.$alliance['alliance_id'].')) AND (d.status<>"-1")';

    if(!$q_diplomacy = $db->query($sql)) {
        message(DATABASE_ERROR, 'Could not query alliance diplomacy data');
    }

    $game->out('
      <table width="300" align="center" border="0" cellpadding="2" cellspacing="2" class="style_inner">
        <tr>
          <td width="160"><b>'.constant($game->sprache("TEXT26")).'</b></td>
          <td width="60" align="center"><b>'.constant($game->sprache("TEXT27")).'</b></td>
          <td width="80" align="center"><b>'.constant($game->sprache("TEXT28")).'</b></td>
        </tr>
    ');

    while($diplomacy = $db->fetchrow($q_diplomacy)) {
        $ap = ($diplomacy['alliance1_id'] == $alliance['alliance_id']) ? 2 : 1;

        $diplomacy_names = array(ALLIANCE_DIPLOMACY_WAR => constant($game->sprache("TEXT29")), ALLIANCE_DIPLOMACY_NAP => constant($game->sprache("TEXT30")), ALLIANCE_DIPLOMACY_PACT => constant($game->sprache("TEXT31")) );

        $game->out('
       <tr>
         <td><a href="'.parse_link('a=stats&a2=viewalliance&id='.$diplomacy['alliance'.$ap.'_id']).'">'.$diplomacy['alliance'.$ap.'_name'].'</a></td>
         <td align="center"><a href="javascript:void(0);">'.$diplomacy_names[$diplomacy['type']].'</a></td>
         <td align="center">'.date('d.m.y', $diplomacy['date']).'</td>
       </tr>
        ');
    }

    $game->out('
      </table></center>
      ');
}







$game->out('</td></tr></table><br><br></td></tr></table>');
}
}


function Show_Player()
{
global $db;
global $game;
global $world;
global $userquery;

global $start;
global $search_name;
global $RACE_DATA;

$_REQUEST['id']=(int)$_REQUEST['id'];

// Men BASIC
Show_Main();

$userquery=$db->query('SELECT * FROM user WHERE user_id="'.$_REQUEST['id'].'" AND (user_active=1 OR user_active=3) LIMIT 1');
$found=$db->num_rows();
if (!isset($_REQUEST['id']) || empty($_REQUEST['id']) || $found!=1) {$game->out('<center><span class="sub_caption">'.constant($game->sprache("TEXT32")).'</span></center>');}
else
{
if (($user = $db->fetchrow($userquery))==false) {$game->out('<center><span class="sub_caption">'.constant($game->sprache("TEXT33")).' (id='.$_REQUEST['id'].'<br>'.constant($game->sprache("TEXT34")).'</span></center>');}
else
{
$query='SELECT * FROM alliance WHERE alliance_id="'.$user['user_alliance'].'"';
$alliance=$db->queryrow($query);

$rank_nr=1;
global $rank_honor;
if ($user['user_honor']>=$rank_honor[0]) $rank_nr=1;
if ($user['user_honor']>=$rank_honor[1]) $rank_nr=2;
if ($user['user_honor']>=$rank_honor[2]) $rank_nr=3;
if ($user['user_honor']>=$rank_honor[3]) $rank_nr=4;
if ($user['user_honor']>=$rank_honor[4]) $rank_nr=5;
if ($user['user_honor']>=$rank_honor[5]) $rank_nr=6;
if ($user['user_honor']>=$rank_honor[6]) $rank_nr=7;
if ($user['user_honor']>=$rank_honor[7]) $rank_nr=8;
if ($user['user_honor']>=$rank_honor[8]) $rank_nr=9;
if ($user['user_honor']>=$rank_honor[9]) $rank_nr=10;

$game->out('
<table border=0 cellpadding=2 cellspacing=2 class ="style_outer"><tr><td width=450>
<center><span class="caption">'.constant($game->sprache("TEXT35")).' '.$user['user_name'].'</span><br><span class="sub_caption2">'.( ($user['user_attack_protection']>=$game->config['tick_id']) ? '<span style="color: red">'.constant($game->sprache("TEXT36")).'</span>' : '' ).'</span></center>
</td></tr><tr><td>
<table border=0 cellpadding=1 cellspacing=1 width=450 class ="style_inner"><tr>
<td colspan=2><img src="'.$game->GFX_PATH.'rank_'.$rank_nr.'.jpg"></td></tr><tr>');

//avatar:
if (!empty($user['user_avatar']))
{
$info = getimagesize_remote($user['user_avatar']);

if ($info[0]>0 && $info[1]>0 && $info[0]<=150 && $info[1]<=250)
{
$game->out('<td width='.$info[0].'><img src="'.$user['user_avatar'].'"></td><td width=25></td><td width=425-'.$info[0].' valign=top>');
}
else if ($info[0]>0 && $info[1]>0)
{
$width=150;
$height=150;
	if ($info[0]>$info[1]) {$height = 150 * ($info[1] / $info[0]);}
 	else {$width = 150 * ($info[0] / $info[1]);}


$game->out('<td width='.$width.'><img src="'.$user['user_avatar'].'" width="'.$width.'" height="'.$height.'"></td><td width=25></td><td width=425-'.$width.' valign=top>');
}
else $game->out('<td width=200></td><td width=250 valign=top>');
}
else $game->out('<td width=200></td><td width=250 valign=top>');

$rasse=$RACE_DATA[$user['user_race']][0];


$planets=$db->queryrow('SELECT count(planet_id) AS num FROM planets WHERE planet_owner="'.$user['user_id'].'"');


// Onlinestatus:
    $sql = 'SELECT d.*,
                   u1.user_name AS user1_name, u1.user_alliance AS user1_aid, u1.user_points AS user1_points, u1.user_planets AS user1_planets, u1.user_honor AS user1_honor, a1.alliance_tag AS user1_atag,
                   u2.user_name AS user2_name, u2.user_alliance AS user2_aid, u2.user_points AS user2_points, u2.user_planets AS user2_planets, u2.user_honor AS user2_honor, a2.alliance_tag AS user2_atag
            FROM (user_diplomacy d)
            INNER JOIN (user u1) ON u1.user_id = d.user1_id
            LEFT JOIN (alliance a1) ON a1.alliance_id = u1.user_alliance
            INNER JOIN (user u2) ON u2.user_id = d.user2_id
            LEFT JOIN (alliance a2) ON a2.alliance_id = u2.user_alliance
            WHERE ((d.user1_id = '.$user['user_id'].') AND (d.user2_id = '.$game->player['user_id'].')) OR
                  ((d.user1_id = '.$game->player['user_id'].') AND (d.user2_id = '.$user['user_id'].'))';
    if(!$q_diplomacy = $db->query($sql)) {
        message(DATABASE_ERROR, 'Could not query diplomacy private data');
    }

    $allied=$db->num_rows();
    $diplomacy = $db->fetchrow($q_diplomacy);
    if(!$diplomacy['accepted']) $allied=0;


if ($user['last_active']>(time()-60*3)) $status='<span style="color: green">'.constant($game->sprache("TEXT37")).'</span>';
else if ($user['last_active']>(time()-60*9)) $status='<span style="color: orange">'.constant($game->sprache("TEXT38")).'</span>';
else $status='<span style="color: red">'.constant($game->sprache("TEXT39")).'</span>';
if (($game->player['user_alliance']!=$user['user_alliance'] || $user['user_alliance']<=0) && $game->player['user_auth_level']!=STGC_DEVELOPER && $allied==0) $status='<span style="color: blue">'.constant($game->sprache("TEXT40")).'</span>';

$icq='';

if (isset($user['user_icq']) && !empty($user['user_icq'])) {$icq='<tr>
<td width=70><span class="sub_caption2"><b>'.constant($game->sprache("TEXT41")).'</b></span></td>
<td width=150><span class="sub_caption2"><b><img src="http://web.icq.com/whitepages/online?icq='.$user['user_icq'].'&img=5"> '.$user['user_icq'].'</b></span></td>
</tr>';}

$game->out('
<table border=0 cellpadding=0 cellspacing=0 class="style_inner">
<tr>
<td width=70><span class="text_large">'.constant($game->sprache("TEXT42")).'</td>
<td width=150><span class="text_large">'.$rasse.'</td>
</tr><tr>
<td width=70><span class="text_large">'.constant($game->sprache("TEXT8")).'</b></span></td>
<td width=150><span class="text_large"><a href="'.parse_link('a=stats&a2=viewalliance&id='.$alliance['alliance_id'].'').'"><span class="text_large">'.$alliance['alliance_tag'].' ('.$alliance['alliance_name'].')</a></span></td>
</tr><tr>
<td width=70><span class="text_large">'.constant($game->sprache("TEXT11")).'</b></span></td>
<td width=150><span class="text_large">'.$user['user_points'].'</td>
</tr><tr>
<td width=70><span class="text_large">'.constant($game->sprache("TEXT19")).'</b></span></td>
<td width=150><span class="text_large">'.$planets['num'].'</td>
</tr><tr>
<td width=70><span class="text_large">'.constant($game->sprache("TEXT43")).'</b></span></td>
<td width=150><span class="text_large">'.$user['user_honor'].'</td>
</tr><tr>
<td width=70><span class="text_large">'.constant($game->sprache("TEXT88")).'</b></span></td>
<td width=150><span class="text_large">'.$user['num_auctions'].'</td>
</tr><tr>
<td width=70><span class="text_large">'.constant($game->sprache("TEXT44")).'</b></span></td>
<td width=150><span class="text_large">'.$status.'</td>
</tr><span class="text_large">'.$icq.'
</table>

</td></tr></table></td></tr>
<tr>
<td>
<center>
<span class="sub_caption"><b><a href="'.parse_link('a=messages&a2=newpost&receiver='.$user['user_name'].'').'"><span style="color: white">'.constant($game->sprache("TEXT45")).'</span></a>&nbsp;&nbsp;&nbsp;
<a href="'.parse_link('a=user_diplomacy&suggest='.$user['user_id'].'').'"><span style="color: white">'.constant($game->sprache("TEXT46")).'</span></a></b>
');


$game->out('</center></span></td>
</tr>

<tr><td align="center"><b><a onclick="Popup(this.href);return false" href="modules/auctions.popup.php?user='.$user['user_id'].'">Aktuelle Auktionen anzeigen</a></b></td></tr><tr><td>&nbsp;</td></tr>

</table><br>
<table border=0 cellpadding=2 cellspacing=2 width=450 class="style_outer">
  <tr>
    <td><span class="sub_caption">'.constant($game->sprache("TEXT47")).'</span></td>
  </tr>
  <tr>
    <td>
      <table border=0 cellpadding=2 cellspacing=2 width=450 class="style_inner">
        <tr>
          <td width=150 align="center"><b>'.constant($game->sprache("TEXT48")).'</b></td>
          <td width=150 align="center"><b>'.constant($game->sprache("TEXT49")).'</b></td>
          <td width=150 align="center"><b>'.constant($game->sprache("TEXT50")).'</b></td>
        </tr>

');

$user_id = $game->player['user_id'];

$historyqry=$db->query('SELECT * FROM userally_history WHERE user_id = "'.$user['user_id'].'"');

while(($history = $db->fetchrow($historyqry))==true)
{
  $game->out('<tr><td align="center">'.get_alliance_name($history['alliance_id']).'</td><td align="center">'.date('d.m.y H:i', $history['join_date']+7200).'</td><td align="center">'.( ($history['leave_date']==0) ? '-' : ''.date('d.m.y H:i', $history['leave_date']+7200).'' ).'</td></tr>');
}

$historyqry2=$db->query('SELECT * FROM userally_history WHERE user_id = "'.$user['user_id'].'"');

if(($history2 = $db->fetchrow($historyqry2))!=true) {
  $game->out('<tr><td align="center"></td><td align="center">'.constant($game->sprache("TEXT51")).'</td><td align="center"></td></tr>');
}

$game->out('

      </table>
    </td>
  </tr>
</table><br>

<table border=0 cellpadding=2 cellspacing=2 class="style_outer"><tr>
<td width=450>
<span class="sub_caption">'.constant($game->sprache("TEXT52")).'</span><br>
<center><a href="usermap.php?user='.$user['user_name'].'&size=6&map" target=_blank><img src="usermap.php?user='.$user['user_name'].'&size=1" border=0></a>
</center>
</td></tr></table>
<br>




<table border=0 cellpadding=2 cellspacing=2 class="style_outer"><tr>
<td width=450>
<span class="sub_caption">'.constant($game->sprache("TEXT53")).'</span><br>');

$game->out('
<center><table border=1 cellpadding=0 cellspacing=0 class="style_inner">
<tr><td width=80><b>'.constant($game->sprache("TEXT53")).'</td><td width=200><b>'.constant($game->sprache("TEXT18")).'</td><td width=50><b>'.constant($game->sprache("TEXT11")).'</td><td width=50><b>'.constant($game->sprache("TEXT55")).'</td></tr>
');

$planetquery=$db->query('SELECT pl.*, sys.system_x, sys.system_y FROM (planets pl) LEFT JOIN (starsystems sys) on sys.system_id = pl.system_id  WHERE pl.planet_owner="'.$user['user_id'].'" ORDER BY pl.planet_name');
while(($planet = $db->fetchrow($planetquery))==true)
{
$game->out('<td>'.$game->get_sector_name($planet['sector_id']).':'.$game->get_system_cname($planet['system_x'],$planet['system_y']).':'.($planet['planet_distance_id'] + 1).'</td><td><a href="'.parse_link('a=tactical_cartography&planet_id='.encode_planet_id($planet['planet_id'])).'">');
if($planet['planet_name']=="") $planet['planet_name']="(unbennant)";
$game->out($planet['planet_name'].'</a></td><td>'.$planet['planet_points'].'</td><td>'.strtoupper($planet['planet_type']).'</td></tr>');
}
$game->out('</td></tr></table>
</td></tr></table>

<br>
<table border=0 cellpadding=2 cellspacing=2 class = "style_outer"><tr><td width=450>
<span class="sub_caption">'.constant($game->sprache("TEXT56")).'</span><br>
<center><table border=0 cellpadding=0 cellspacing=0 valign=left class="style_inner">
<tr><td width=120><b>'.constant($game->sprache("TEXT18")).'</b></td><td width=70><b>'.constant($game->sprache("TEXT11")).'</b></td><td width=70><b>'.constant($game->sprache("TEXT19")).'</b></td><td width=70><b>'.constant($game->sprache("TEXT43")).'</b></td><td width=80><b>'.constant($game->sprache("TEXT57")).'</b></td></tr>
<tr><td></td></tr>');

    $sql = 'SELECT d.*,
                   u1.user_name AS user1_name, u1.user_alliance AS user1_aid, u1.user_points AS user1_points, u1.user_planets AS user1_planets, u1.user_honor AS user1_honor, a1.alliance_tag AS user1_atag,
                   u2.user_name AS user2_name, u2.user_alliance AS user2_aid, u2.user_points AS user2_points, u2.user_planets AS user2_planets, u2.user_honor AS user2_honor, a2.alliance_tag AS user2_atag
            FROM (user_diplomacy d)
            INNER JOIN (user u1) ON u1.user_id = d.user1_id
            LEFT JOIN (alliance a1) ON a1.alliance_id = u1.user_alliance
            INNER JOIN (user u2) ON u2.user_id = d.user2_id
            LEFT JOIN (alliance a2) ON a2.alliance_id = u2.user_alliance
            WHERE d.user1_id = '.$user['user_id'].' OR
                  d.user2_id = '.$user['user_id'];

    if(!$q_diplomacy = $db->query($sql)) {
        message(DATABASE_ERROR, 'Could not query diplomacy private data');
    }
    while($diplomacy = $db->fetchrow($q_diplomacy)) {
    $opid = ($diplomacy['user1_id'] == $user['user_id']) ? 2 : 1;
    if($diplomacy['accepted']) {
        $game->out('<tr><td><a href="'.parse_link('a=stats&a2=viewplayer&id='.$diplomacy['user'.$opid.'_id']).'">'.$diplomacy['user'.$opid.'_name'].'</a>'.( ($diplomacy['user'.$opid.'_aid']) ? ' [<a href="'.parse_link('a=stats&a2=viewalliance&id='.$diplomacy['user'.$opid.'_aid']).'">'.$diplomacy['user'.$opid.'_atag'].'</a>]' : '' ).'</td><td>'.$diplomacy['user'.$opid.'_points'].'</td><td>'.$diplomacy['user'.$opid.'_planets'].'</td><td>'.$diplomacy['user'.$opid.'_honor'].'</td><td>'.gmdate('d.m.Y', $diplomacy['date']).'</td></tr>');
    }


    }






$game->out('
</table></center>
</td></tr>
</table>
');

$user['user_signature'] = strip_tags($user['user_signature']);
$user['user_signature'] = str_replace("<script", "<!--", $user['user_signature']); 
$user['user_signature'] = str_replace("</script>", "-->", $user['user_signature']);
$user['user_signature'] = str_replace("javascript", "", $user['user_signature']); 


$game->out('<br>
<table border=0 cellpadding=2 cellspacing=2 class = "style_outer"><tr><td width=450>
<span class="sub_caption">'.constant($game->sprache("TEXT58")).'</span><br>
<center>
<table border=0 cellpadding=0 cellspacing=0 class = "style_inner"><tr><td width=350><br>'.stripslashes(nl2br($user['user_signature'])).'
</td></tr></table>
</td></tr>
</table>

<br>
<table border=0 cellpadding=2 cellspacing=2 class="style_outer"><tr><td width=450>
<span class="sub_caption">'.constant($game->sprache("TEXT59")).'</span><br>

    <center><table border=0 cellspacing=0 cellpadding=0>

	<tr height=15><td width=150><center><b>'.$user['user_gallery_name_1'].'</b></td><td width=150><center><b>'.$user['user_gallery_name_2'].'</b></td></tr>
	<tr height=100><td width=150><center><a href="gallery.php?f=gallery/img_'.$user['user_id'].'_1.img" target=image onmouseover="return overlib(\''.$user['user_gallery_description_1'].'\', CAPTION, \''.$user['user_gallery_name_1'].'\', WIDTH, 400, '.OVERLIB_STANDARD.');" onmouseout="return nd();">'.GiveThumb('gallery/img_'.$user['user_id'].'_1.img').'</a>
	</td><td width=150><center><a href="gallery.php?f=gallery/img_'.$user['user_id'].'_2.img" target=image onmouseover="return overlib(\''.$user['user_gallery_description_2'].'\', CAPTION, \''.$user['user_gallery_name_2'].'\', WIDTH, 400, '.OVERLIB_STANDARD.');" onmouseout="return nd();">'.GiveThumb('gallery/img_'.$user['user_id'].'_2.img').'</a></td></tr>

	<tr height=20><td>&nbsp;</td><td>&nbsp;</td></tr>

	<tr height=15><td width=150><center><b>'.$user['user_gallery_name_3'].'</b></td><td width=150><center><b>'.$user['user_gallery_name_4'].'</b></td></tr>
	<tr height=100><td width=150><center><a href="gallery.php?f=gallery/img_'.$user['user_id'].'_3.img" target=image onmouseover="return overlib(\''.$user['user_gallery_description_3'].'\', CAPTION, \''.$user['user_gallery_name_3'].'\', WIDTH, 400, '.OVERLIB_STANDARD.');" onmouseout="return nd();">'.GiveThumb('gallery/img_'.$user['user_id'].'_3.img').'</a></td><td width=150><center><a href="gallery.php?f=gallery/img_'.$user['user_id'].'_4.img" target=image onmouseover="return overlib(\''.$user['user_gallery_description_4'].'\', CAPTION, \''.$user['user_gallery_name_4'].'\', WIDTH, 400, '.OVERLIB_STANDARD.');" onmouseout="return nd();">'.GiveThumb('gallery/img_'.$user['user_id'].'_4.img').'</a></td></tr>

	<tr height=30><td>&nbsp;</td><td>&nbsp;</td></tr>

	</table><table border=0 cellspacing=0 cellpadding=0>

	<tr height=15><td width=150><center><b>'.$user['user_gallery_name_5'].'</b></td></tr>
	<tr height=100><td width=150><center><a href="gallery.php?f=gallery/img_'.$user['user_id'].'_5.img" target=image onmouseover="return overlib(\''.$user['user_gallery_description_5'].'\', CAPTION, \''.$user['user_gallery_name_5'].'\', WIDTH, 400, '.OVERLIB_STANDARD.');" onmouseout="return nd();">'.GiveThumb('gallery/img_'.$user['user_id'].'_5.img').'</a></td></tr>
	</table>
	</center>


</td></tr>
</table>

');

}
}
}





function Player_Ranking($focus=0,$search_name="")
{
global $db;
global $game;
global $userquery;
global $RACE_DATA;

$search_name=htmlspecialchars($_REQUEST['search']);
$_REQUEST['start']=$_REQUEST['start'];


if (isset($_REQUEST['start'])) {$focus=$_REQUEST['start'];}
$start=$focus;




if (!isset($_REQUEST['a3']) || empty($_REQUEST['a3'])) $_REQUEST['a3']=1;
$queryfocus=$focus;
if ($focus<=10) $queryfocus=11;

$rankquery=$db->query('SELECT u.*,a.alliance_tag,a.alliance_name,s.id FROM (user u) LEFT JOIN (alliance a) ON a.alliance_id=u.user_alliance LEFT JOIN (spenden s) ON s.name=u.user_name WHERE (u.user_rank_points>="'.($queryfocus-10).'") AND (u.user_active=1 OR u.user_active=3) AND u.user_auth_level<>'.STGC_DEVELOPER.' ORDER by u.user_rank_points ASC LIMIT 20');

$rankpos='user_rank_points';

if (isset($_REQUEST['a3']) && $_REQUEST['a3']==2)
{
$rankquery=$db->query('SELECT u.*,a.alliance_tag,a.alliance_name,s.id FROM (user u) LEFT JOIN (alliance a) ON a.alliance_id=u.user_alliance LEFT JOIN (spenden s) ON s.name=u.user_name WHERE (u.user_rank_planets>="'.($queryfocus-10).'") AND (u.user_active=1 OR u.user_active=3) AND u.user_auth_level<>'.STGC_DEVELOPER.' ORDER by u.user_rank_planets ASC LIMIT 20');
$rankpos='user_rank_planets';
}
else if (isset($_REQUEST['a3']) && $_REQUEST['a3']==3)
{
$rankquery=$db->query('SELECT u.*,a.alliance_tag,a.alliance_name,s.id FROM (user u) LEFT JOIN (alliance a) ON a.alliance_id=u.user_alliance LEFT JOIN (spenden s) ON s.name=u.user_name WHERE (u.user_rank_honor>="'.($queryfocus-10).'") AND (u.user_active=1 OR u.user_active=3)  ORDER by u.user_rank_honor ASC LIMIT 20');
//$rankquery=$db->query('SELECT u.*,a.alliance_tag,a.alliance_name,s.id FROM (user u) LEFT JOIN (alliance a) ON a.alliance_id=u.user_alliance LEFT JOIN (spenden s) ON s.name=u.user_name WHERE (u.user_rank_honor>="'.($queryfocus-10).'") AND (u.user_active=1 OR u.user_active=3) AND u.user_auth_level<>'.STGC_DEVELOPER.' ORDER by u.user_rank_honor ASC LIMIT 20');
$rankpos='user_rank_honor';
}


// Men BASIC
Show_Main();

// Menu PLAYER Specific
$game->out('<center><table boder=0 cellpadding=2 cellspacing=2 class="style_inner" width=300><tr>');

if ((isset($_REQUEST['a3']) && $_REQUEST['a3']==1) || (!isset($_REQUEST['a3'])))
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=player_ranking&a3=1&search='.$search_name).'"><span class="text_large"><u>'.constant($game->sprache("TEXT60")).'</u></a><br>[<a href="/game/include/pdf_ranking.php?action=user&order=points" target="_blank"><b>PDF</b></a>]
</td>');
}
else
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=player_ranking&a3=1&search='.$search_name).'"><span class="text_large">'.constant($game->sprache("TEXT60")).'</a><br>[<a href="/game/include/pdf_ranking.php?action=user&order=points" target="_blank"><b>PDF</b></a>]
</td>');
}

if (isset($_REQUEST['a3']) && $_REQUEST['a3']==2)
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=player_ranking&a3=2&search='.$search_name).'"><span class="text_large"><u>'.constant($game->sprache("TEXT61")).'</u></a><br>[<a href="/game/include/pdf_ranking.php?action=user&order=planets" target="_blank"><b>PDF</b></a>]
</td>');
}
else
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=player_ranking&a3=2&search='.$search_name).'"><span class="text_large">'.constant($game->sprache("TEXT61")).'</a><br>[<a href="/game/include/pdf_ranking.php?action=user&order=planets" target="_blank"><b>PDF</b></a>]
</td>');
}

if (isset($_REQUEST['a3']) && $_REQUEST['a3']==3)
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=player_ranking&a3=3&search='.$search_name).'"><span class="text_large"><u>'.constant($game->sprache("TEXT62")).'</u></a><br>[<a href="/game/include/pdf_ranking.php?action=user&order=honor" target="_blank"><b>PDF</b></a>]
</td>');
}
else
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=player_ranking&a3=3&search='.$search_name).'"><span class="text_large">'.constant($game->sprache("TEXT62")).'</a><br>[<a href="/game/include/pdf_ranking.php?action=user&order=honor" target="_blank"><b>PDF</b></a>]
</td>');
}

$game->out('</tr></table>');


// Men Top20, Eigene Platzierung + Spieler suchen
$game->out('<br><center><table boder=0 cellpadding=2 cellspacing=2  class="style_inner" width=300><tr>

<td width=150 align=middle>
<a href="'.parse_link('a=stats&a2=player_ranking&a3='.$_REQUEST['a3'].'&start=0').'"><span class="text_large">'.constant($game->sprache("TEXT63")).'</a>
</td>
<td width=150 align=middle>
<a href="'.parse_link('a=stats&a2=player_ranking&a3='.$_REQUEST['a3']).'"><span class="text_large">'.constant($game->sprache("TEXT64")).'</a>
</td>
</tr>
<tr><td></td><td></td></tr><tr><td></td><td></td></tr><tr><td></td><td></td></tr><tr><td></td><td></td></tr>
<tr>
<td valign=top><form method="post" action="'.parse_link('a=stats&a2=player_ranking&a3='.$_REQUEST['a3']).'">
<center><input type="text" name="search" size="16" class="field" value="'.htmlspecialchars($_REQUEST['search']).'"></td>
<td><center><input type="submit" name="exec_search" class="button" width=100 value="'.constant($game->sprache("TEXT65")).'"></td></tr></form>

</table>
');
//Tobis spezial, ja mojo reg dich ab a=stats&a2=player_ranking&a3=1
if($search_name!="" && $focus==0)
{
$game->out('<br><center><table boder=0 cellpadding=2 cellspacing=2 class="style_inner" width=300><tr>
<td width=150><b>'.constant($game->sprache("TEXT66")).'</b></td>
</tr>');
$search_sonder=$db->query('SELECT u.user_name FROM (user u)  WHERE u.user_name LIKE "%'.$_REQUEST['search'].'%" ORDER by u.user_name ASC');
while (($user = $db->fetchrow($search_sonder)) != false)
{
$game->out('
<tr>
<td width=150><a href="'.parse_link('a=stats&a2=player_ranking&search='.$user ['user_name'].'&a3='.$_REQUEST['a3']).'">'.$user ['user_name'].'</a>
</td>
</tr>
');
}
$game->out('</table></center>');
}
// Men << >>
$game->out('<br><center><table boder=0 cellpadding=2 cellspacing=2 class="style_inner" width=300><tr>
<td width=150 align=middle><a href="'.parse_link('a=stats&a2=player_ranking&a3='.$_REQUEST['a3'].'&start='.($start-20)).'"><span class="text_large">'.constant($game->sprache("TEXT67")).'</a>
</td><td width=150 align=middle><a href="'.parse_link('a=stats&a2=player_ranking&a3='.$_REQUEST['a3'].'&start='.($start+20)).'"><span class="text_large">'.constant($game->sprache("TEXT68")).'</a>
</td></tr></table>');

$game->out('<br><center><table boder=0 cellpadding=1 cellspacing=1 width=550 class="style_inner">
<tr>
<td width=50>
<b>'.constant($game->sprache("TEXT69")).'</b>
</td>
<td width=150>
<b>'.constant($game->sprache("TEXT18")).'</b>
</td>
<td width=150>
<b>'.constant($game->sprache("TEXT42")).'</b>
</td>
<td width=100>
<b>'.constant($game->sprache("TEXT8")).'</b>
</td>
<td width=100>
<b>'.constant($game->sprache("TEXT19")).'</b>
</td>
<td width=75>
<b>'.constant($game->sprache("TEXT11")).'</b>
</td>
<td width=75>
<b>'.constant($game->sprache("TEXT43")).'</b>
</td>
<td width=200 align=center style="border-color:#FFFFFF; border-width:1px;">
<b>'.constant($game->sprache("TEXT70")).'</b>
</td></tr>
');

$tickquery = 'SELECT tick_id FROM config';

if(($config_data = $db->queryrow($tickquery)) === false) {
  message(DATABASE_ERROR, 'Could not query config data for tick_id');
}


while (($user = $db->fetchrow($rankquery)) != false)
{
$tag="-";
if (!empty($user['alliance_tag'])) $tag='['.$user['alliance_tag'].']';

$game->out('
<tr>
<td>
&nbsp;'.$user[$rankpos].'
</td><td>');


if($user['user_vacation_start']<=$config_data['tick_id'] && $user['user_vacation_end']>=$config_data['tick_id']) {

  if (isset($_REQUEST['start']) || $user[$rankpos]!=$focus) {

    $game->out('<a href="'.parse_link('a=stats&a2=viewplayer&id='.$user['user_id'].'').'">'.$user['user_name'].'</a><b><a href="javascript:void(0);" onmouseover="return overlib(\''.constant($game->sprache("TEXT71")).'\', CAPTION, \'Urlaub\', WIDTH, 200, '.OVERLIB_STANDARD.');" onmouseout="return nd();"><span style="color:white">*</span></a></b>'.(isset($user['id']) ? '&nbsp;&nbsp;<a href="javascript:void(0);" onmouseover="return overlib(\''.constant($game->sprache("TEXT72")).'\', CAPTION, \'Auszeichnung\', WIDTH, 200, '.OVERLIB_STANDARD.');" onmouseout="return nd();"><img src="|game_url/stgc4_gfx/skin1/reward.jpg" border=0></a>' : ''));
  }
  else {
  $game->out('<a href="'.parse_link('a=stats&a2=viewplayer&id='.$user['user_id'].'').'"><span style="color:yellow">'.$user['user_name'].'</a><b><a href="javascript:void(0);" onmouseover="return overlib(\''.constant($game->sprache("TEXT71")).'\', CAPTION, \'Urlaub\', WIDTH, 200, '.OVERLIB_STANDARD.');" onmouseout="return nd();"><span style="color:white">*</span></a></b></span></color>'.(isset($user['id']) ? '&nbsp;&nbsp;<a href="javascript:void(0);" onmouseover="return overlib(\''.constant($game->sprache("TEXT72")).'\', CAPTION, \'Auszeichnung\', WIDTH, 200, '.OVERLIB_STANDARD.');" onmouseout="return nd();"><img src="|game_url/stgc4_gfx/skin1/reward.jpg" border=0></a>' : '')); }
}
else
{
  if (isset($_REQUEST['start']) || $user[$rankpos]!=$focus) {

    $game->out('<a href="'.parse_link('a=stats&a2=viewplayer&id='.$user['user_id'].'').'">'.$user['user_name'].'</a>'.(isset($user['id']) ? '&nbsp;&nbsp;<a href="javascript:void(0);" onmouseover="return overlib(\''.constant($game->sprache("TEXT72")).'\', CAPTION, \'Auszeichnung\', WIDTH, 200, '.OVERLIB_STANDARD.');" onmouseout="return nd();"><img src="|game_url/stgc4_gfx/skin1/reward.jpg" border=0></a>' : ''));
  }
  else {
  $game->out('<a href="'.parse_link('a=stats&a2=viewplayer&id='.$user['user_id'].'').'"><span style="color:yellow">'.$user['user_name'].'</a></span></color>'.(isset($user['id']) ? '&nbsp;&nbsp;<a href="javascript:void(0);" onmouseover="return overlib(\''.constant($game->sprache("TEXT72")).'\', CAPTION, \'Auszeichnung\', WIDTH, 200, '.OVERLIB_STANDARD.');" onmouseout="return nd();"><img src="|game_url/stgc4_gfx/skin1/reward.jpg" border=0></a>' : '')); }
}

$game->out('</td>
<td>');
$game->out($RACE_DATA[$user['user_race']][0].'</td><td>');
if ($tag!="-") $game->out('<a href="'.parse_link('a=stats&a2=viewalliance&id='.$user['user_alliance'].'').'">'.$tag.'</a>');
$game->out('</td>
<td>
'.$user['user_planets'].'
</td>
<td>
'.$user['user_points'].'
</td>
<td>
'.$user['user_honor'].'
</td>
');
$gradi = array ( 0 => constant($game->sprache("TEXT73")) , 1 => constant($game->sprache("TEXT74")) , 2 => constant($game->sprache("TEXT75")) , 
3 => constant($game->sprache("TEXT76")) , 4 => constant($game->sprache("TEXT77")), 5 => constant($game->sprache("TEXT78")), 6 => constant($game->sprache("TEXT79")), 
7 => constant($game->sprache("TEXT80")), 8 => constant($game->sprache("TEXT81")), 9 => constant($game->sprache("TEXT82")) );
$rank_nr=1;
if ($user['user_honor']>0 && $user['user_honor']<5000)
{
if ($user['user_points']/$user['user_honor']>=100) $rank_nr=1;
if ($user['user_points']/$user['user_honor']<100 && $user['user_points']/$user['user_honor']>=50) $rank_nr=2;
if ($user['user_points']/$user['user_honor']<50 && $user['user_points']/$user['user_honor']>=20) $rank_nr=3;
if ($user['user_points']/$user['user_honor']<20) $rank_nr=4;
}
else
{
if ($user['user_honor']>5000 && $user['user_honor']<=6000) $rank_nr=5;
if ($user['user_honor']>6000 && $user['user_honor']<=7000) $rank_nr=6;
if ($user['user_honor']>7000 && $user['user_honor']<=8000) $rank_nr=7;
if ($user['user_honor']>8000 && $user['user_honor']<=9000) $rank_nr=8;
if ($user['user_honor']>9000 && $user['user_honor']<=10000) $rank_nr=9;
if ($user['user_honor']>10000) $rank_nr=10;
}
$game->out('<td align=center style="border-bottom-color:#C0C0C0; border-bottom-width:1px; 
"><i>'.$gradi[$rank_nr-1].'</td></tr>');
}

$game->out('</table>');

// Men << >>
$game->out('<br><center><table boder=0 cellpadding=2 cellspacing=2 class="style_inner" width=300><tr>
<td width=150 align=middle><a href="'.parse_link('a=stats&a2=player_ranking&a3='.$_REQUEST['a3'].'&start='.($start-20)).'"><span class="text_large">'.constant($game->sprache("TEXT67")).'</a>
</td><td width=150 align=middle><a href="'.parse_link('a=stats&a2=player_ranking&a3='.$_REQUEST['a3'].'&start='.($start+20)).'"><span class="text_large">'.constant($game->sprache("TEXT68")).'</a>
</td></tr></table>');
}














function Alliance_Ranking($focus=0, $highlight)
{
global $db;
global $game;


$search_name=htmlspecialchars($_REQUEST['search']);
$_REQUEST['start']=$_REQUEST['start'];
if (isset($_REQUEST['start'])) {$focus=$_REQUEST['start'];}
$start=$focus;

if (!isset($_REQUEST['a3']) || empty($_REQUEST['a3'])) $_REQUEST['a3']=1;
$queryfocus=$focus;
if ($focus<=10) $queryfocus=11;

$rankquery=$db->query('SELECT * FROM alliance WHERE (alliance_rank_points>="'.($queryfocus-10).'") AND (alliance_rank_points<="'.($queryfocus+10).'") ORDER by alliance_rank_points ASC LIMIT 20');

$rankpos='alliance_rank_points';

if (isset($_REQUEST['a3']) && $_REQUEST['a3']==2)
{
$rankquery=$db->query('SELECT * FROM alliance WHERE (alliance_rank_planets>="'.($queryfocus-10).'") AND (alliance_rank_planets<="'.($queryfocus+10).'") ORDER by alliance_rank_planets ASC LIMIT 20');
$rankpos='alliance_rank_planets';
}
else if (isset($_REQUEST['a3']) && $_REQUEST['a3']==3)
{
$rankquery=$db->query('SELECT * FROM alliance WHERE (alliance_rank_honor>="'.($queryfocus-10).'") AND (alliance_rank_honor<="'.($queryfocus+10).'") ORDER by alliance_rank_honor ASC LIMIT 20');
$rankpos='alliance_rank_honor';
}
else if (isset($_REQUEST['a3']) && $_REQUEST['a3']==4)
{
$rankquery=$db->query('SELECT * FROM alliance WHERE (alliance_rank_points_avg>="'.($queryfocus-10).'") AND (alliance_rank_points_avg<="'.($queryfocus+10).'") ORDER by alliance_rank_points_avg ASC LIMIT 20');
$rankpos='alliance_rank_points_avg';
}


// Men BASIC
Show_Main();

// Menu ALLIANCE Specific
$game->out('<center><table boder=0 cellpadding=2 cellspacing=2 class="style_inner" width=400><tr>');

if ((isset($_REQUEST['a3']) && $_REQUEST['a3']==1) || (!isset($_REQUEST['a3'])))
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=alliance_ranking&a3=1&search='.$search_name).'"><span class="text_large"><u>'.constant($game->sprache("TEXT83")).'</u></b></a><br><b>[<a href="/game/include/pdf_ranking.php?action=alliance&order=points" target="_blank">PDF</a>]</b>
</td>');
}
else
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=alliance_ranking&a3=1&search='.$search_name).'"><span class="text_large">'.constant($game->sprache("TEXT83")).'</a><br><b>[<a href="/game/include/pdf_ranking.php?action=alliance&order=points" target="_blank">PDF</a>]</b>
</td>');
}

if ((isset($_REQUEST['a3']) && $_REQUEST['a3']==4))
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=alliance_ranking&a3=4&search='.$search_name).'"><span class="text_large"><u>'.constant($game->sprache("TEXT84")).'</u><br></b></a><b>[<a href="/game/include/pdf_ranking.php?action=alliance&order=points_avg" target="_blank">PDF</a>]</b>
</td>');
}
else
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=alliance_ranking&a3=4&search='.$search_name).'"><span class="text_large">'.constant($game->sprache("TEXT84")).'</a><br><b>[<a href="/game/include/pdf_ranking.php?action=alliance&order=points_avg" target="_blank">PDF</a>]</b>
</td>');
}

if (isset($_REQUEST['a3']) && $_REQUEST['a3']==2)
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=alliance_ranking&a3=2&search='.$search_name).'"><span class="text_large"><u>'.constant($game->sprache("TEXT61")).'</u><br></b></a><b>[<a href="/game/include/pdf_ranking.php?action=alliance&order=planets" target="_blank">PDF</a>]</b>
</td>');
}
else
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=alliance_ranking&a3=2&search='.$search_name).'"><span class="text_large">'.constant($game->sprache("TEXT61")).'</a><br><b>[<a href="/game/include/pdf_ranking.php?action=alliance&order=planets" target="_blank">PDF</a>]</b>
</td>');
}

if (isset($_REQUEST['a3']) && $_REQUEST['a3']==3)
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=alliance_ranking&a3=3&search='.$search_name).'"><span class="text_large"><u>'.constant($game->sprache("TEXT62")).'</u><br></b></a><b>[<a href="/game/include/pdf_ranking.php?action=alliance&order=honor" target="_blank">PDF</a>]</b>
</td>');
}
else
{
$game->out('<td width=100 align=middle>
<a href="'.parse_link('a=stats&a2=alliance_ranking&a3=3&search='.$search_name).'"><span class="text_large">'.constant($game->sprache("TEXT62")).'</a><br><b>[<a href="/game/include/pdf_ranking.php?action=alliance&order=honor" target="_blank">PDF</a>]</b>
</td>');
}

$game->out('</tr></table>');



// Men Top20, Eigene Platzierung + Allianz suchen
$game->out('<br><center><table boder=0 cellpadding=2 cellspacing=2 class="style_inner" width=300><tr>

<td width=150 align=middle>
<a href="'.parse_link('a=stats&a2=alliance_ranking&a3='.$_REQUEST['a3'].'&start=0').'"><span class="text_large">'.constant($game->sprache("TEXT63")).'</a>
</td>
<td width=150 align=middle>
<a href="'.parse_link('a=stats&a2=alliance_ranking&a3='.$_REQUEST['a3']).'"><span class="text_large">'.constant($game->sprache("TEXT64")).'</a>
</td>
</tr>
<tr><td></td><td></td></tr><tr><td></td><td></td></tr><tr><td></td><td></td></tr><tr><td></td><td></td></tr>
<tr>
<td valign=top><form method="post" action="'.parse_link('a=stats&a2=alliance_ranking&a3='.$_REQUEST['a3']).'">
<center><input type="text" name="search" size="16" class="field" value="'.$_REQUEST['search'].'"></td>
<td><center><input type="submit" name="exec_search" class="button" width=100 value="'.constant($game->sprache("TEXT85")).'"></td></tr></form>

</table>
');



// Men << >>
$game->out('<br><center><table boder=0 cellpadding=2 cellspacing=2 class="style_inner" width=300><tr>
<td width=150 align=middle><a href="'.parse_link('a=stats&a2=alliance_ranking&a3='.$_REQUEST['a3'].'&start='.($start-20)).'"><span class="text_large">'.constant($game->sprache("TEXT67")).'</a>
</td><td width=150 align=middle><a href="'.parse_link('a=stats&a2=alliance_ranking&a3='.$_REQUEST['a3'].'&start='.($start+20)).'"><span class="text_large">'.constant($game->sprache("TEXT68")).'</a>
</td></tr></table>');

$game->out('<br><center><table boder=0 cellpadding=1 cellspacing=1 width=600 class="style_inner">
<tr>
<td width=50>
<b>'.constant($game->sprache("TEXT69")).'</b>
</td>
<td width=100>
<b>'.constant($game->sprache("TEXT86")).'</b>
</td>
<td width=175>
<b>'.constant($game->sprache("TEXT18")).'</b>
</td>

<td width=50>
<b>'.constant($game->sprache("TEXT10")).'</b>
</td>
<td width=50>
<b>'.constant($game->sprache("TEXT19")).'</b>
</td>
<td width=50>
<b>'.constant($game->sprache("TEXT43")).'</b>
</td>
<td width=75>
<b>'.constant($game->sprache("TEXT11")).'</b>
<td width=75>
<b>'.constant($game->sprache("TEXT87")).'</b>
</td>
</tr>
');


while (($alliance = $db->fetchrow($rankquery)) != false)
{

$game->out('
<tr>
<td>
&nbsp;'.$alliance[$rankpos].'
</td>
<td>');
if (isset($_REQUEST['start']) || $alliance['alliance_id']!=$highlight)
$game->out('<a href="'.parse_link('a=stats&a2=viewalliance&id='.$alliance['alliance_id'].'').'">'.$alliance['alliance_tag'].'</a>');
else
$game->out('<a href="'.parse_link('a=stats&a2=viewalliance&id='.$alliance['alliance_id'].'').'"><span style="color:yellow">'.$alliance['alliance_tag'].'</a></span></color>');
$game->out('</td>
<td>');

if (isset($_REQUEST['start']) || $alliance['alliance_id']!=$highlight)
$game->out('<a href="'.parse_link('a=stats&a2=viewalliance&id='.$alliance['alliance_id'].'').'">'.$alliance['alliance_name'].'</a>');
else
$game->out('<a href="'.parse_link('a=stats&a2=viewalliance&id='.$alliance['alliance_id'].'').'"><span style="color:yellow">'.$alliance['alliance_name'].'</a></span></color>');
$game->out('</td>
<td>');


$game->out($alliance['alliance_member'].'</td>
<td>
'.$alliance['alliance_planets'].'
</td>
<td>
'.$alliance['alliance_honor'].'
</td>
<td>
'.$alliance['alliance_points'].'
</td>
<td>
'.round($alliance['alliance_points']/$alliance['alliance_member']).'
</td>
</tr>');

}

$game->out('</table>');

// Men << >>
$game->out('<br><center><table boder=0 cellpadding=2 cellspacing=2 class="style_inner" width=300><tr>
<td width=150 align=middle><a href="'.parse_link('a=stats&a2=alliance_ranking&a3='.$_REQUEST['a3'].'&start='.($start-20)).'"><span class="text_large">'.constant($game->sprache("TEXT67")).'</a>
</td><td width=150 align=middle><a href="'.parse_link('a=stats&a2=alliance_ranking&a3='.$_REQUEST['a3'].'&start='.($start+20)).'"><span class="text_large">'.constant($game->sprache("TEXT68")).'</a>
</td></tr></table>');

}















////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////

$sub_action = (!empty($_GET['a2'])) ? $_GET['a2'] : 'main';

if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) $_REQUEST['search']=htmlspecialchars(mysql_real_escape_string($_REQUEST['search']));
if (isset($_REQUEST['start'])) $_REQUEST['start']=(int)$_REQUEST['start'];
if (isset($_REQUEST['id'])) $_REQUEST['id']=(int)$_REQUEST['id'];

if (!isset($_REQUEST['a3']) || empty($_REQUEST['a3'])) $_REQUEST['a3']=1;


if ($sub_action=='main' || $sub_action=='player_ranking')
{
if (!isset($_REQUEST['search']) || empty($_REQUEST['search'])) {$_REQUEST['search']=$game->player['user_name'];}
if ($_REQUEST['a3']!=2 && $_REQUEST['a3']!=3) {$nr=$db->queryrow('SELECT user_rank_points AS focus FROM user WHERE user_name LIKE "'.$_REQUEST['search'].'" LIMIT 1');}
if ($_REQUEST['a3']==2) $nr=$db->queryrow('SELECT user_rank_planets AS focus FROM user WHERE user_name LIKE "'.$_REQUEST['search'].'" LIMIT 1');
if ($_REQUEST['a3']==3) $nr=$db->queryrow('SELECT user_rank_honor AS focus FROM user WHERE user_name LIKE "'.$_REQUEST['search'].'" LIMIT 1');
$focus=$nr['focus'];

Player_Ranking($focus,$_REQUEST['search']);
}

if ($sub_action=='alliance_ranking')
{

if (!isset($_REQUEST['search']) || empty($_REQUEST['search']))
{

if ($game->player['user_alliance']>0) 	$alliance=$db->queryrow('SELECT alliance_tag FROM alliance WHERE alliance_id="'.$game->player['user_alliance'].'"');
else 									$alliance=$db->queryrow('SELECT alliance_tag FROM alliance ORDER BY alliance_rank_points ASC LIMIT 1');
$_REQUEST['search']=htmlspecialchars($alliance['alliance_tag']);
}
if ($_REQUEST['a3']!=2 && $_REQUEST['a3']!=3) {$nr=$db->queryrow('SELECT alliance_rank_points AS focus, alliance_id AS highlight FROM alliance WHERE alliance_tag LIKE "'.$_REQUEST['search'].'" LIMIT 1');}
if ($_REQUEST['a3']==2) $nr=$db->queryrow('SELECT alliance_rank_planets AS focus, alliance_id AS highlight FROM alliance WHERE alliance_tag LIKE "'.$_REQUEST['search'].'" LIMIT 1');
if ($_REQUEST['a3']==3) $nr=$db->queryrow('SELECT alliance_rank_honor AS focus, alliance_id AS highlight FROM alliance WHERE alliance_tag LIKE "'.$_REQUEST['search'].'" LIMIT 1');
if ($_REQUEST['a3']==4) $nr=$db->queryrow('SELECT alliance_rank_points_avg AS focus, alliance_id AS highlight FROM alliance WHERE alliance_tag LIKE "'.$_REQUEST['search'].'" LIMIT 1');
$focus=$nr['focus'];
$highlight=$nr['highlight'];
//SELECT * FROM alliance WHERE (alliance_rank_points_avg>="'.($queryfocus-10).'") AND (alliance_rank_points_avg<="'.($queryfocus+10).'") ORDER by alliance_rank_points_avg ASC
Alliance_Ranking($focus,$highlight);
}

if ($sub_action=='viewplayer') Show_Player();
if ($sub_action=='viewalliance') Show_Alliance();

?>
