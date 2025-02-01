<?php
if (!defined('MODX_BASE_PATH')) {
	http_response_code(403);
	die('Go fuck yourself'); 
}

global $manager_language;
$e = &$modx->event;

$param = $modx->event->params;

$id = isset($param["id_module"]) ? intval($param["id_module"]) : 0;
$title = isset($param["title"]) ? (string) $param["title"] : "Меню";
$sort = isset($param["sort"]) ? intval($param["sort"]) : 0;

switch($e->name){
	case "OnManagerMenuPrerender":
		$table = $modx->getFullTablename('site_modules');
		$result = $modx->db->select('id,icon,name,disabled', $table, "id = '$id'");
		if( $modx->db->getRecordCount( $result ) >= 1 ):
			if($row = $modx->db->getRow( $result )):
				$disabled = intval($row["disabled"]);
				$strip = $modx->stripAlias($row["name"]);
				if(!$disabled):
					// Построение
					$menuparams = [
						'render_' . $strip,
						'main',
						$row["icon"] . $title,
						'index.php?a=112&id=' . $id,
						$strip,
						'',
						'',
						'main',
						0,
						$sort,
						''
					];
					$params['menu']['render_' . $strip] = $menuparams;
					$modx->event->output(serialize($params['menu']));
				endif;
			endif;
		endif;
		break;
}