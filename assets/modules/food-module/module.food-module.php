<?php
if (!defined('MODX_BASE_PATH')) {
	http_response_code(403);
	exit();
}

if (!$modx->hasPermission('exec_module')) {
	$modx->sendRedirect('index.php?a=106');
}

if (!is_array($modx->event->params)) {
	$modx->event->params = [];
}

// Получаем модуль
function getModule() {
	$evo = EvolutionCMS();
	$id = $_GET['id'] ? (int)$_GET['id'] : 0;
	$result = $evo->db->select('id,name,icon', $evo->getFullTablename('site_modules'), "id='$id'");
	if($row = $evo->db->getRow($result)):
		return $row;
	endif;
	return false;
}

$params = $modx->event->params;
$params["folders"] = $params["folders"] ? (string)$params["folders"] : 'food';
$opts = [
	'options' => [
		'min_range' => 1,
		'max_range' => 5,
		'default'   => 2
	],
];

$autodelete = filter_var($params["autodelete"], FILTER_VALIDATE_BOOLEAN);
$autodelete_year = filter_var($params["autodelete_year"], FILTER_VALIDATE_INT, $opts);

define("FOOD_AUTODELETE", $autodelete);
define("FOOD_AUTODELETE_YEAR", $autodelete_year);

// Директория модуля
$base_path = str_replace('\\', '/', dirname(__FILE__)) . '/';
define('SCHOOL_FOLDERS_BASE_PATH', $base_path);

define('FOOD_MOD_PATH', str_replace(MODX_BASE_PATH, '/', SCHOOL_FOLDERS_BASE_PATH));

include_once SCHOOL_FOLDERS_BASE_PATH . "lib/SchoolFood.php";

// Разрешённые директории
$access_path = preg_split('/[\s,;]+/', $params["folders"]);
$access_path = array_map('SchoolFood::TranslitFile', $access_path);

global $_lang, $content, $_style, $modx_lang_attribute, $lastInstallTime, $manager_language;

// Языковые пакеты
include_once SCHOOL_FOLDERS_BASE_PATH . "lang/english.inc.php";
if(!isset($manager_language) || !file_exists(SCHOOL_FOLDERS_BASE_PATH . "lang/".$manager_language.".inc.php")) {
	$manager_language = "english"; // if not set, get the english language file.
}

if($manager_language!="english" && file_exists(SCHOOL_FOLDERS_BASE_PATH . "lang/".$manager_language.".inc.php")) {
	include_once SCHOOL_FOLDERS_BASE_PATH . "lang/" . $manager_language.".inc.php";
}

if(file_exists(SCHOOL_FOLDERS_BASE_PATH . "js/lang/" . $manager_language . ".json")):
	$langJson = FOOD_MOD_PATH . "js/lang/" . $manager_language . ".json";
else:
	$langJson = FOOD_MOD_PATH . "js/lang/english.json";
endif;

// Получаем данные модуля
$module = getModule();
// Иконка
$module["icon"] = trim($module["icon"]) ? trim($module["icon"]) : "fa fa-cube";

// Получить данные запроса ($_GET, $_POST, $_REQUEST)
$mode = $_REQUEST['mode'] ?? '';
$path = $_REQUEST['path'] ?? '';
$file = $_REQUEST['file'] ?? '';
$newfile = $_REQUEST['newfile'] ?? '';

// Директория по умолчанию ()
$dir = $_REQUEST['path'] ?? '';

// Корневая директория
$startpath = MODX_BASE_PATH;
// Получаем рабочую директорию
if (isset($_REQUEST['path']) && !empty($_REQUEST['path'])) {
	$dir = trim($dir, " \n\r\t\v\x00\\/|\"'`!@#$%^&*()_-+={}[]|<>?.,");
}

// Проверяем, относится ли полученная директория к разрешённым
if($dir && !in_array($dir, $access_path)):
	$modx->sendRedirect('index.php?a=112&id=' . $module['id']);
	exit();
endif;

// Проверяем возможность чтения директории
if (!is_readable(MODX_BASE_PATH . $dir)) {
	$modx->webAlertAndQuit($_lang["not_readable_dir"]);
}

// Создаём SchoolFood
$food = new SchoolFood(
	MODX_BASE_PATH,
	array(
		// Просматриваемая директория
		"path"              => $dir,
		// Автоудаление
		"autodelete"        => FOOD_AUTODELETE,
		// Сколько лет
		"year"              => FOOD_AUTODELETE_YEAR,
		// Разрешённые директории
		"access_path"       => $access_path
	),
	array(
		"delete"               => $_lang["sch_food_delete"],
		"not_delete"           => $_lang["sch_food_not_delete"],
		"not_file_delete"      => $_lang["sch_food_not_file_delete"],
		"rename"               => $_lang["sch_food_rename"],
		"not_rename"           => $_lang["sch_food_not_rename"],
		"access_rename"        => $_lang["sch_food_access_rename"],
		"access_rename_ext"    => $_lang["sch_food_access_rename_ext"],
		"access_path"          => $_lang["sch_food_access_path"],
		"access_file"          => $_lang["sch_food_access_file"],
		"upload"               => $_lang["sch_food_upload"],
		"not_upload"           => $_lang["sch_food_not_upload"],
		"file_exists"          => $_lang["sch_food_file_exists"],
		"not_found"            => $_lang["sch_food_not_found"],
		"same_name"            => $_lang["sch_food_same_name"]
	)
);

// Директории созданы в SchoolFood классе. Удалены старые файлы.
// Запись .htaccess
include_once SCHOOL_FOLDERS_BASE_PATH . "htaccess/.htaccess.php";

foreach ($access_path as $value):
	try {
		$path = MODX_BASE_PATH . $value;
		if(is_dir($path)):
			// Записываем .htaccess
			@file_put_contents($path . "/.htaccess", $htaccess);
			@chmod($path . "/.htaccess", 0644);
		endif;
	} catch (\Exception $e) {
		$application->redirect('index.php?option=' . $option, \JText::_('COM_FOOD_ERROR'), 'error');
	}
endforeach;

$data = array();

switch ($mode) {
	case 'upload':
		$data = $food->uploadFiles()->getData()->output;
		break;
	case 'rename':
		$data = $food->renameFile($file, $newfile)->getData()->output;
		break;
	case 'delete':
		$data = $food->deleteFile($file)->getData()->output;
		break;
	default:
		$data = $food->getData()->output;
		break;
}

$lng = json_encode(array(
	"sch_tool_issue_delete"                    => $_lang["sch_tool_issue_delete"],
	"sch_tool_new_filename"                    => $_lang["sch_tool_new_filename"],
	"sch_tool_not_upload_file_type"            => $_lang["sch_tool_not_upload_file_type"],
	"sch_tool_name"                            => $_lang["sch_tool_name"],
	"sch_tool_type"                            => $_lang["sch_tool_type"],
	"sch_tool_select"                          => $_lang["sch_tool_select"],
	"sch_tool_one"                             => $_lang["sch_tool_one"],
	"sch_tool_two"                             => $_lang["sch_tool_two"],
	"sch_tool_three"                           => $_lang["sch_tool_three"],
	"sch_tool_uploads"                         => $_lang["sch_tool_uploads"],
	"sch_tool_select_uploads"                  => $_lang["sch_tool_select_uploads"],
	"sch_tool_dragdrop_title"                  => $_lang["sch_tool_dragdrop_title"],
	"sch_tool_dragdrop_before"                 => $_lang["sch_tool_dragdrop_before"],
	"sch_tool_by"                              => $_lang["sch_tool_by"],
	"sch_tool_all"                             => $_lang["sch_tool_all"],
	"sch_tool_tools"                           => $_lang["sch_tool_tools"],
	"sch_tool_column_visibility"               => $_lang["sch_tool_column_visibility"],
	"sch_tool_export"                          => $_lang["sch_tool_export"],
	"sch_tool_export_xlsx"                     => $_lang["sch_tool_export_xlsx"],
	"sch_tool_export_pdf"                      => $_lang["sch_tool_export_pdf"],
	"sch_tool_print"                           => $_lang["sch_tool_print"],
), JSON_PRETTY_PRINT);

ob_start();
// $onManagerMainFrameHeaderHTMLBlock
// Подключение файлов
include_once MODX_MANAGER_PATH . 'includes/header.inc.php';
include_once SCHOOL_FOLDERS_BASE_PATH . 'tmpl/template.php';
include_once MODX_MANAGER_PATH . 'includes/footer.inc.php';

echo ob_get_clean();
