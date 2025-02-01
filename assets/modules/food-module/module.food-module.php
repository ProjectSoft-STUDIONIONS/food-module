<?php
if (!defined('MODX_BASE_PATH')) {
	http_response_code(403);
	die('Go fuck yourself'); 
}

if (!$modx->hasPermission('exec_module')) {
	$modx->sendRedirect('index.php?a=106');
}

if (!is_array($modx->event->params)) {
	$modx->event->params = [];
}
$params = $modx->event->params;
$params["folders"] = $params["folders"] ? (string)$params["folders"] : 'food';

// Директория модуля
$base_path = str_replace('\\', '/', dirname(__FILE__)) . '/';
define('SCHOOL_FOLDERS_BASE_PATH', $base_path);

// Разрешённые директории
$access_path = explode(',', $params["folders"]);

global $_lang, $content, $_style, $manager_language, $new_folder_permissions, $new_file_permissions, $startpath, $exts, $msg;

// Языковые пакеты
include_once SCHOOL_FOLDERS_BASE_PATH . "lang/english.inc.php";
if(!isset($manager_language) || !file_exists(SCHOOL_FOLDERS_BASE_PATH . "lang/".$manager_language.".inc.php")) {
	$manager_language = "english"; // if not set, get the english language file.
}

if($manager_language!="english" && file_exists(SCHOOL_FOLDERS_BASE_PATH . "lang/".$manager_language.".inc.php")) {
	include_once SCHOOL_FOLDERS_BASE_PATH . "lang/" . $manager_language.".inc.php";
}

// Массивы директорий и файлов
$directorys = [];
$files = [];
// Вывод сообщений
$all = [];
$all['error'] = "";
$all['success'] = "";

// path join
function path_join(...$base) {
	$result = [];
	foreach ($base as $n):
		$result[] = rtrim( $n, '/' );
	endforeach;
	return implode('/', $result);
}

function string_join(...$string) {
	$result = implode("<br>", $string);
    return $result;
}

// Удаление предыдущей директории из строки.
function removeLastPath($string)
{
	$pos = strrpos($string, '/');
	if ($pos !== false) {
		$path = substr($string, 0, $pos);
	} else {
		$path = false;
	}
	return $path;
}

// Получаем имя директории
function getDirName($string) {
	$string = rtrim($string, '/');
	$string = str_replace(MODX_BASE_PATH, '', $string);
	return $string;
}

// Размер директории
function dir_size($path) {
	$path = rtrim($path, '/');
	$size = 0;
	$dir = opendir($path);
	if (!$dir) {
		return 0;
	}
	
	while (false !== ($file = readdir($dir))) {
		if ($file == '.' || $file == '..') {
			continue;
		} elseif (is_dir($path . $file)) {
			$size += dir_size($path . DIRECTORY_SEPARATOR . $file);
		} else {
			$size += filesize($path . DIRECTORY_SEPARATOR . $file);
		}
	}
	closedir($dir);
	return $size;
}

// Соответствует ли директория к правилам просмотра
function checkedPath($string, $access_path = []) {
	if($string == MODX_BASE_PATH):
		return true;
	endif;
	$string = getDirName($string);
	return in_array($string, $access_path);
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

function renameFile($new_file="", $file=""){

	global $_lang, $startpath, $new_file_permissions, $exts;
	$modx = evolutionCMS();
	$msg = '';
	$all = [];
	$all['error'] = "";
	$all['success'] = "";
	// Если имена одинаковые - ничего не делаем. Выходим
	if($file == $new_file):
		return $all;
	endif;
	// Исходный файл
	$old_pathinfo = pathinfo($file);
	$old_pathinfo['extension'] = trim($old_pathinfo['extension']);
	// Переименование только pdf или xlsx
	if(!in_array($old_pathinfo['extension'], $exts)):
		$all['error'] = string_join("<strong>Запрет на переименование файла</strong>", $file);
		return $all;
	endif;
	// Транслит имени файла
	$pthinfo = pathinfo($new_file);
	$f_name = $pthinfo['filename'];
	$f_name = $modx->stripAlias($f_name);
	// На всякий случай
	// Удаляет специальные символы
	$f_name = preg_replace('/[^A-Za-z0-9\-\_.]/', '', $f_name);
	// Заменяет несколько тире на одно
	$f_name = preg_replace('/-+/', '-', $f_name);
	// Заменяет несколько нижних тире на одно
	$f_name = preg_replace('/_+/', '_', $f_name);
	// Запрещаем переименовывать расширение.
	// Объединяем новое имя с расширением исходного файла
	$new_file = $f_name . "." . $old_pathinfo['extension'];
	// Если имена одинаковые - выходим c ошибкой
	if($file == $new_file):
		$all['error'] = string_join("<strong>$new_file</strong>", $_lang["sch_file_duble"]);
		return $all;
	endif;
	$oFile = path_join($startpath, $file);
	$nFile = path_join($startpath, $new_file);
	// Существование исходного файла
	if(is_file($oFile)):
		// Продолжаем
		if(!is_file($nFile)):
			// Продолжаем
			// Переименовываем
			if(@rename($oFile, $nFile)):
				// Удачно
				$all['success'] = string_join("<strong>" . $_lang['sch_file_rename'] . "</strong>", "$file => $new_file");
			else:
				// Не удачно
				$all['error'] = string_join("<strong>" . $_lang['sch_file_error_rename'] . "</strong>", "$file => $new_file");
			endif;
		else:
			// Уже есть данный файл
			$all['error'] = string_join("<strong>$new_file</strong>", $_lang["sch_file_duble"]);
		endif;
	else:
		// Не существует
		$all['error'] = string_join("<strong>$file</strong>", $_lang["sch_file_notfound"]);
	endif;
	return $all;
}

function deleteFile($file) {
	global $_lang, $startpath, $new_file_permissions, $exts;
	$all = [];
	$all['error'] = "";
	$all['success'] = "";
	$old_file = path_join($startpath, $file);
	if(is_file($old_file)):
		if(@unlink($old_file)):
			$all['success'] = "<strong>Файл удалён</strong><br>$file";
		else:
			$all['error'] = "<strong>Файл не удалён</strong><br>$file";
		endif;
	else:
		$all['error'] = "<strong>Файл не существует</strong><br>$file";
	endif;
	return $all;
}

function fileupload()
{
	$modx = evolutionCMS();
	global $_lang, $startpath, $new_file_permissions, $exts;
	$msg = '';
	$all = [];
	$all['error'] = "";
	$all['success'] = "";
	foreach ($_FILES['userfiles']['name'] as $i => $name):
		if (empty($_FILES['userfiles']['tmp_name'][$i])) continue;
		$msg = "";
		$userfile= array();
		$name = strtolower($_FILES['userfiles']['name'][$i]);
		$name = $modx->stripAlias($name);
		// На всякий случай
		// Удаляет специальные символы
		$name = preg_replace('/[^A-Za-z0-9\-\_.]/', '', $name);
		// Заменяет несколько тире на одно
		$name = preg_replace('/-+/', '-', $name);
		// Заменяет несколько нижних тире на одно
		$name = preg_replace('/_+/', '_', $name);
		$extension = pathinfo($name, PATHINFO_EXTENSION);
		$userfile['name'] = $name;
		$userfile['type'] = $_FILES['userfiles']['type'][$i];
		$userfile['tmp_name'] = $_FILES['userfiles']['tmp_name'][$i];
		$userfile['error'] = $_FILES['userfiles']['error'][$i];
		$userfile['size'] = $_FILES['userfiles']['size'][$i];
		$userfile['extension'] = $extension;
		$path = $startpath . '/' . $userfile['name'];
		$userfile['startpath'] = $startpath;
		$userfile['path'] = $path;
		$userfile['permissions'] = $new_file_permissions;
		$userfilename = $userfile['tmp_name'];
		if(is_uploaded_file($userfilename)):
			if(in_array($extension, $exts)):
				if (@move_uploaded_file($userfile['tmp_name'], $userfile['path'])):
					if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'):
						@chmod($userfile['path'], $new_file_permissions);
					endif;
					$msg = '<dl class="dl-horizontal">';
					$msg .= '<dt>' . $_lang["sch_file_upload"] . '</dt>';
					$msg .= '<dd>' . $userfile['name'] . '</dd>';
					$msg .= '</dl>';
					$all['success'] .= $msg;
					$modx->invokeEvent('OnFileManagerUpload', array(
						'filepath' => $userfile['startpath'],
						'filename' => $userfile['name']
					));
				else:
					$msg = '<dl class="dl-horizontal">';
					$msg .= '<dt>' . $_lang["sch_file_upload_error"] . '</dt>';
					$msg .= '<dd>' . $userfile['name'] . '</dd>';
					$msg .= '</dl>';
					$all['error'] .= $msg;
				endif;
			else:
				$msg = '<dl class="dl-horizontal">';
				$msg .= '<dt>' . $_lang["sch_file_upload_error"] . '</dt>';
				$msg .= '<dd>' . $userfile['name'] . '</dd>';
				$msg .= '</dl>';
				$all['error'] .= $msg;
			endif;
		else:
			$msg = '<dl class="dl-horizontal">';
			$msg .= '<dt>' . $_lang["sch_file_upload_error"] . '</dt>';
			$msg .= '<dd>' . $userfile['name'] . '</dd>';
			$msg .= '</dl>';
			$all['error'] .= $msg;
		endif;
	endforeach;
	return $all;
}

$module = getModule();

$module["icon"] = trim($module["icon"]) ? trim($module["icon"]) : "fa fa-cube";

// Prevent php 8 warnings
$_POST['mode'] = $_POST['mode'] ?? '';
$_GET['mode'] = $_GET['mode'] ?? '';
$_REQUEST['mode'] = $_REQUEST['mode'] ?? '';
$_REQUEST['path'] = $_REQUEST['path'] ?? '';

$path = $_REQUEST['path'];

$startpath = MODX_BASE_PATH;
// Получаем рабочую директорию
if (isset($_REQUEST['path']) && !empty($_REQUEST['path'])) {
	$_REQUEST['path'] = str_replace('..', '', $_REQUEST['path']);
	$startpath = is_dir($_REQUEST['path']) ? $_REQUEST['path'] : removeLastPath($_REQUEST['path']);
} 

if(!checkedPath($startpath, $access_path)):
	$modx->sendRedirect('index.php?a=112&id=' . $module['id'] . '&mode=dir&path=' . MODX_BASE_PATH);
endif;

// Формируем путь к директории
$startpath = $startpath == '/' ? '/' : rtrim($startpath, '/');

$startpath = checkedPath($startpath, $access_path) ? $startpath : rtrim(MODX_BASE_PATH, '/');

if (!is_readable($startpath)) {
	$modx->webAlertAndQuit($_lang["not_readable_dir"]);
}

// Разрешённые файлы
$exts = ["xlsx", "pdf"];

setlocale(LC_NUMERIC, 'C');

ob_start();

// Загрузка файлов
if($_REQUEST['mode'] == 'upload'):
	if(checkedPath($path, $access_path)):
		$print = $path;
		$all = fileupload();
	endif;
endif;

// Переименовывание файла
if($_REQUEST['mode'] == 'rename'):
	if($_REQUEST['newfile'] && $_REQUEST['file']):
		$all = renameFile($_REQUEST['newfile'], $_REQUEST['file']);
	endif;
endif;

// Удаление файла
if($_REQUEST['mode'] == 'delete'):
	if($_REQUEST['file']):
		$all = deleteFile($_REQUEST['file']);
	endif;
endif;

// Чтение директории
// Выводим директории только в корне
$file_path = ltrim($startpath, MODX_BASE_PATH);
$dir = new DirectoryIterator($startpath);

foreach ($dir as $fileinfo):
	if (!$fileinfo->isDot()):
		if($fileinfo->isDir()):
			$filename = $fileinfo->getFilename();
			if(in_array($filename, $access_path)):
				$directorys[] = $fileinfo->getFilename();
			endif;
		else:
			// Выводим только нужные файлы (pdf, xlsx)
			if(checkedPath($startpath, $access_path)):
				// Файлы
				$ext = strtolower($fileinfo->getExtension());
				if(in_array($ext, $exts)):
					$files[] = $fileinfo->getFilename();
				endif;
			endif;
		endif;
	endif;
endforeach;

// Сортировка директорий
sort($directorys);
// Сортировка файлов
rsort($files);
// Имя директории
$title_path = pathinfo($startpath, PATHINFO_BASENAME);
// Заголовок
$title = checkedPath($startpath, $access_path) ? 'Директория: ' . $title_path : 'Директории';

// Подключение файлов
include_once MODX_MANAGER_PATH . 'includes/header.inc.php';
include_once SCHOOL_FOLDERS_BASE_PATH . 'template.php';
include_once MODX_MANAGER_PATH . 'includes/footer.inc.php';

// Вывод
echo ob_get_clean();
