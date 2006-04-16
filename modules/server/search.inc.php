<?php
include_once('modules/mastersearch2/class_mastersearch2.php');
$ms2 = new mastersearch2();

function PWIcon($pw){
  global $dsp, $templ;
  
  if ($pw) {
    $templ['ms2']['icon_name'] = 'locked';
    $templ['ms2']['icon_title'] = 'Unprotected';
  } else {
    $templ['ms2']['icon_name'] = 'unlocked';
    $templ['ms2']['icon_title'] = 'Protected';
  }
  return $dsp->FetchModTpl('mastersearch2', 'result_icon');
}

function ServerType ($type) {
	switch ($type) {
		default: return "???"; break;
		case "gameserver": return "Game"; break;
		case "ftp": return "FTP"; break;
		case "irc": return "IRC"; break;
		case "web": return "Web"; break;
		case "proxy": return "Proxy"; break;
		case "misc": return "Misc"; break;
	}
}

function ServerStatus () {
	global $cfg, $line;

	// Wenn Intranetversion, erreichbarkeit testen
	if ($cfg["sys_internet"] == 0 and (!get_cfg_var("safe_mode"))) {
		include_once("modules/server/ping_server.inc.php");	   
		ping_server($line['ip'], $line['port']);

		if ($line['available'] == 1) return "<div class=\"tbl_green\">Online</div>";
		elseif ($line['available'] == 2) return "<div class=\"tbl_red\">Port Offline</div>";
		else return "<div class=\"tbl_red\">IP Offline</div>";
	} else  return "-";
}


$ms2->query['from'] = "{$config["tables"]["server"]} AS s LEFT JOIN {$config["tables"]["user"]} AS u ON s.owner = u.userid";

$ms2->config['EntriesPerPage'] = 30;

$ms2->AddTextSearchField($lang['server']['details_name'], array('s.caption' => 'like', 's.ip' => 'like'));
$ms2->AddTextSearchField($lang['server']['details_owner'], array('u.username' => '1337', 'u.name' => 'like', 'u.firstname' => 'like'));
$ms2->AddTextSearchDropDown($lang['server']['details_servertype'], 's.type', array('' => $lang['sys']['all'], 'gameserver' => 'Game', 'ftp' => 'FTP', 'irc' => 'IRC', 'web' => 'Web', 'proxy' => 'Proxy', 'misc' => 'Misc'));
$ms2->AddTextSearchDropDown('PW', 's.pw', array('' => $lang['sys']['all'], '0' => $lang['sys']['no'], '1' => $lang['sys']['yes']));

$ms2->AddSelect('u.userid');
$ms2->AddResultField($lang['server']['details_name'], 's.caption');
$ms2->AddResultField($lang['server']['details_servertype'], 's.type', 'ServerType');
$ms2->AddResultField($lang['server']['details_ipaddr'], 's.ip');
$ms2->AddResultField($lang['server']['details_port'], 's.port');
$ms2->AddResultField($lang['server']['details_owner'], 'u.username', 'UserNameAndIcon');
$ms2->AddResultField('PW', 's.pw', 'PWIcon');
$ms2->AddResultField($lang['server']['details_state'], 's.available', 'ServerStatus');

$ms2->AddIconField('details', 'index.php?mod=server&action=show_details&serverid=', $lang['ms2']['details']);
if ($auth['type'] >= 2) $ms2->AddIconField('edit', 'index.php?mod=server&action=change&step=2&serverid=', $lang['ms2']['edit']);
if ($auth['type'] >= 3) $ms2->AddIconField('delete', 'index.php?mod=server&action=delete&step=2&serverid=', $lang['ms2']['delete']);

$ms2->PrintSearch('index.php?mod=server&action=show', 's.serverid');
?>