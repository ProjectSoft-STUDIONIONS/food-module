<?php
if (!defined('MODX_BASE_PATH')) {
	http_response_code(403);
	exit();
}

global $manager_language;
$e = &$modx->event;

$param = $modx->event->params;

$id = isset($param["id_module"]) ? intval($param["id_module"]) : 0;
$title = isset($param["title"]) ? (string) $param["title"] : "Меню";
$sort = isset($param["sort"]) ? intval($param["sort"]) : 0;
// Показать меню
$show = isset($param["show"]) ? intval($param["show"]) : 0;

if(!function_exists('formatFoodString')):
	function formatFoodString($str)
    {
        $str = trim($str);
        $str = stripslashes($str);
        $str = htmlspecialchars($str);
        return $str;
    }
endif;

switch($e->name){
	case "OnManagerMenuPrerender":
		$table = $modx->getFullTablename('site_modules');
		$result = $modx->db->select('id,icon,name,disabled', $table, "id = '$id'");
		if( $modx->db->getRecordCount( $result ) >= 1 ):
			if($row = $modx->db->getRow( $result )):
				$disabled = intval($row["disabled"]);
				$strip = $modx->stripAlias($row["name"]);
				$row["icon"] = $row["icon"] ? ($row["icon"] == "" ? "fa fa-folder-open" : $row["icon"]) : "fa fa-folder-open";
				if(!$disabled):
					// Если показываем
					if($show):
						// Построение
						$menuparams = [
							$strip,
							'main',
							'<i class="' . $row["icon"]. '"></i>' . $title,
							'?a=112&id=' . $id,
							$strip,
							'',
							'',
							'main',
							0,
							$sort,
							''
						];
						$params['menu'][$strip] = $menuparams;
						$modx->event->output(serialize($params['menu']));
					endif;
				endif;
			endif;
		endif;
		break;
	case "OnManagerMainFrameHeaderHTMLBlock":
		$idval = intval(formatFoodString(isset($_GET["id"]) ? $_GET["id"] : "0"));
		$css = 'assets/modules/food-module/css/main.min.css';
		$file_css = MODX_BASE_PATH . $css;
		if($modx->manager->action == 112 && $idval == $id):
			$viewer_html = "";
			$viewer_css = "viewer/app.min.css";
			$file_viewer_css = MODX_BASE_PATH . $viewer_css;
			if(is_file($file_viewer_css)):
				$viewerVers = filemtime($file_viewer_css);
				$viewer_html = "<link type=\"text/css\" rel=\"stylesheet\" href=\"/" . $viewer_css . "?" . $viewerVers . "\">" . PHP_EOL;
			endif;
			// /viewer/app.min.css
			$lcss_time = filemtime($file_css);
			$modx->event->addOutput(
				"<!-- FoodModuleMenu Start -->" . PHP_EOL .
				"<link type=\"text/css\" rel=\"stylesheet\" href=\"/" . $css . "?" . $lcss_time . "\">" . PHP_EOL . $viewer_html .
				"<!-- FoodModuleMenu End -->" . PHP_EOL
			);
		endif;
		break;
}
