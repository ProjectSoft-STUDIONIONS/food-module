<?php
if (!defined('MODX_BASE_PATH')) {
	http_response_code(403);
	exit();
}
ini_set('default_charset','UTF-8');
if (!$modx->hasPermission('exec_module')) {
	$modx->sendRedirect('index.php?a=106');
}

if (!is_array($modx->event->params)) {
	$modx->event->params = [];
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

// Вывод сообщений
$all = [];
$all['error'] = "";
$all['success'] = "";

// Директория модуля
$base_path = str_replace('\\', '/', dirname(__FILE__)) . '/';
define('SCHOOL_FOLDERS_BASE_PATH', $base_path);

// Разрешённые директории
$access_path = preg_split('/[\s,;]+/', $params["folders"]);

global $_lang, $content, $_style, $modx_lang_attribute, $lastInstallTime, $manager_language, $startpath, $exts, $msg, $all;

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

// path join
function path_join(...$base) {
	$result = [];
	foreach ($base as $n):
		$result[] = rtrim( $n, '/' );
	endforeach;
	return implode('/', $result);
}

function string_join(...$string) {
	$result = "";
	try {
		$result = "<div>" . implode("<br>", $string) . "</div>";
	}catch(Exception $e){}
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
	global $_lang, $startpath, $exts, $all;
	$evo = evolutionCMS();
	$msg = '';
	// Если имена одинаковые - ничего не делаем. Выходим
	if($file == $new_file):
		return;
	endif;
	// Исходный файл
	$old_pathinfo = pathinfo($file);
	$old_pathinfo['extension'] = trim($old_pathinfo['extension']);
	// Переименование только pdf или xlsx
	if(!in_array($old_pathinfo['extension'], $exts)):
		$all['error'] .= string_join("<strong>" . $_lang['sch_file_error_perms_rename'] . "</strong>", $file);
		return;
	endif;
	// Транслит имени файла
	$pthinfo = pathinfo($new_file);
	$f_name = $pthinfo['filename'];

	$nameparts = explode('.', $f_name);
		$nameparts = array_map(array(
			$evo,
			'stripAlias'
		), $nameparts, array('file_manager')
	);
	$f_name = implode('.', $nameparts);
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
		$all['error'] .= string_join("<strong>$new_file</strong>", $_lang["sch_file_duble"]);
		return;
	endif;
	$oFile = path_join($startpath, $file);
	$nFile = path_join($startpath, $new_file);
	// Существование исходного файла
	if(is_file($oFile)):
		// Продолжаем
		if(!is_file($nFile)):
			// Переименовываем
			if(@rename($oFile, $nFile)):
				// Удачно
				$all['success'] .= string_join("<strong>" . $_lang['sch_file_rename'] . "</strong>", "$file => $new_file");
			else:
				// Не удачно
				$all['error'] .= string_join("<strong>" . $_lang['sch_file_error_rename'] . "</strong>", "$file => $new_file");
			endif;
		else:
			// Уже есть данный файл
			$all['error'] .= string_join("<strong>$new_file</strong>", $_lang["sch_file_duble"]);
		endif;
	else:
		// Не существует
		$all['error'] .= string_join("<strong>$file</strong>", $_lang["sch_file_notfound"]);
	endif;
	return;
}

// Удаление файла
function deleteFile($file, $auto = false) {
	global $_lang, $startpath, $exts, $all;
	$old_file = path_join($startpath, $file);
	if(is_file($old_file)):
		if(@unlink($old_file)):
			$all['success'] .= string_join("<strong>" . ($auto ? $_lang["sch_autodelete_title"] . "<br>" : "") . $_lang["sch_file_delete"] . "</strong>" , $file);
		else:
			$all['error'] .= string_join("<strong>" . ($auto ? $_lang["sch_autodelete_title"] . "<br>" : "") . $_lang["sch_file_not_delete"] . "</strong>", $file);
		endif;
	else:
		$all['error'] .= string_join("<strong>" . ($auto ? $_lang["sch_autodelete_title"] . "<br>" : "") . $_lang["sch_file_notfound"] . "</strong>", $file);
	endif;
	return;
}

// Загрузка файлов
function fileupload()
{
	$evo = evolutionCMS();
	global $_lang, $startpath, $exts, $all;
	$msg = '';
	foreach ($_FILES['userfiles']['name'] as $i => $name):
		if (empty($_FILES['userfiles']['tmp_name'][$i])) continue;
		$msg = "";
		$userfile= array();
		$nameparts = explode('.', $name);
		$nameparts = array_map(array(
			$evo,
			'stripAlias'
		), $nameparts, array('file_manager'));
		$name = implode('.', $nameparts);
		// На всякий случай
		// Специальные символы.
		$name = preg_replace('/[^A-Za-z0-9\-\_.]/', '', $name);
		// несколько тире на одно
		$name = preg_replace('/-+/', '-', $name);
		// несколько нижних тире на одно
		$name = trim(preg_replace('/_+/', '_', $name));
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
		$userfile['permissions'] = octdec($evo->getConfig('new_file_permissions'));
		$userfilename = $userfile['tmp_name'];
		if(is_uploaded_file($userfilename)):
			if(in_array($extension, $exts)):
				if (@move_uploaded_file($userfile['tmp_name'], $userfile['path'])):
					if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'):
						@chmod($userfile['path'], $userfile['permissions']);
					endif;
					$msg = '<dl class="dl-horizontal">';
					$msg .= '<dt>' . $_lang["sch_file_upload"] . '</dt>';
					$msg .= '<dd>' . $userfile['name'] . '</dd>';
					$msg .= '</dl>';
					$all['success'] .= $msg;
					$evo->invokeEvent('OnFileManagerUpload', array(
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
	return;
}

// Получаем данные модуля
$module = getModule();
// Иконка
$module["icon"] = trim($module["icon"]) ? trim($module["icon"]) : "fa fa-cube";

foreach($access_path as $t_dir):
	$t_dir = path_join(MODX_BASE_PATH, $t_dir);
	if(!is_dir($t_dir)):
		// Создаём директорию
		@mkdir($t_dir, 0755, true);
		// Приеняем права
		@chmod($t_dir, 0755);
	endif;
endforeach;

// Получить данные запроса ($_GET, $_POST, $_REQUEST)
$_POST['mode'] = $_POST['mode'] ?? '';
$_GET['mode'] = $_GET['mode'] ?? '';
$_REQUEST['mode'] = $_REQUEST['mode'] ?? '';
$_REQUEST['path'] = $_REQUEST['path'] ?? '';

// Директория по умолчанию ()
$path = $_REQUEST['path'];

// Корневая директория
$startpath = MODX_BASE_PATH;
// Получаем рабочую директорию
if (isset($_REQUEST['path']) && !empty($_REQUEST['path'])) {
	$_REQUEST['path'] = str_replace('..', '', $_REQUEST['path']);
	$startpath = is_dir(MODX_BASE_PATH . $_REQUEST['path']) ? MODX_BASE_PATH . $_REQUEST['path'] : removeLastPath(MODX_BASE_PATH . $_REQUEST['path']);
}
// Проверяем, относится ли полученная директория к разрешённым
if(!checkedPath($startpath, $access_path)):
	$modx->sendRedirect('index.php?a=112&id=' . $module['id']);
	exit();
endif;

// Формируем путь к директории
$startpath = $startpath == '/' ? '/' : rtrim($startpath, '/');

// Проверяем возможность чтения директории
if (!is_readable($startpath)) {
	$modx->webAlertAndQuit($_lang["not_readable_dir"]);
}

// Разрешённые файлы
$exts = ["xlsx", "pdf"];

// Установка локали
setlocale(LC_NUMERIC, 'C');

// Загрузка файлов
if($_REQUEST['mode'] == 'upload'):
	if(checkedPath($path, $access_path)):
		$print = $path;
		fileupload();
	endif;
endif;

// Переименовывание файла
if($_REQUEST['mode'] == 'rename'):
	if($_REQUEST['newfile'] && $_REQUEST['file']):
		renameFile($_REQUEST['newfile'], $_REQUEST['file']);
	endif;
endif;

// Удаление файла
if($_REQUEST['mode'] == 'delete'):
	if($_REQUEST['file']):
		deleteFile($_REQUEST['file']);
	endif;
endif;

// Начало сбора для вывода
ob_start();

// Чтение директории
// Выводим директории только в корне
$file_path = $path ? "/" . $path : "/";
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
					// Проверить дату (год) в имени файла
					$name = $fileinfo->getFilename();
					$re = '/^(?:[\w]+)?(\d{4})/';
					preg_match($re, $name, $matches, PREG_UNMATCHED_AS_NULL);
					// Если есть 4 цифры в имени файла
					if($matches):
						// Год сейчас
						$year = intval(date("Y", time()));
						// Год в имени файла
						$file_year = intval($matches[1]);
						// Если разница лет больше/равно 2 года.
						if($year - $file_year > FOOD_AUTODELETE_YEAR && FOOD_AUTODELETE):
							// Удаляем файл
							//$file_absolute = $fileinfo->getRealPath();
							//$file_absolute = path_join($startpath, $name);

							deleteFile($name, true);
							//@unlink($file_absolute);
						else:
							// Добавляем файл в отображение
							$files[] = $name;
						endif;
					else:
						// Добавляем файл в отображение
						$files[] = $name;
					endif;
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
$title = checkedPath($startpath, $access_path) ? $_lang["sch_directory"] . ': <code>/' . $title_path . '/</code>&nbsp;<a href="/' . $title_path . '/" target="_blank" class="food-icon food-icon-new-window" title="' . $_lang["sch_new_window"] . '"></a>' : $_lang["sch_directorys"];

// Подключение файлов
include_once MODX_MANAGER_PATH . 'includes/header.inc.php';
include_once SCHOOL_FOLDERS_BASE_PATH . 'template.php';
include_once MODX_MANAGER_PATH . 'includes/footer.inc.php';

// Вывод
echo ob_get_clean();
