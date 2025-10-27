<?php
/**
 * Класс для чтения папок питания.
 * Разрабатывается под:
 * 		Evolution CMS
 * 		Joomla! CMS
 * 		WordPress CMS
 * 		возможно и другие...
 */
class SchoolFood {
	/**
	 * $base_path должен вести к корню сайта.
	 * Только при этом условии класс будет работать правильно
	 */
	// Разрешённые расширения файлов
	const EXT_FILES = ["xlsx", "pdf"];
	// Вывод. Возваращаем массив
	// Вывод заполняется строго в классе.
	public $output = array(
		// Разрешённые директории
		"access_path"  => array("food"),
		// Список файлов
		"files"        => array(),
		// Список директорий
		"directory"    => array(),
		// Сообщения
		"message"      => array(
			// Удачные
			"success"  => array(),
			// Ошибки
			"error"    => array()
		),
		// В какой директории находимся
		"path"         => ""
	);
	public $path = "";
	// Параметры
	private $params = array(
		// Просматриваемая директория
		"path"              => "",
		// Автоудаление
		"autodelete"        => true,
		// Сколько лет
		"year"              => 4,
		// Разрешённые директории
		"access_path"       => array("food")
	);
	// Язык (массив)
	private $lang = array(
		"delete"               => "Файл удалён",
		"not_delete"           => "Файл не удалён",
		"not_file_delete"      => "Файл удалить нельзя",
		"rename"               => "Файл переименован",
		"not_rename"           => "Не удалось переименовать файл",
		"access_rename"        => "Файл переименовать нельзя",
		"access_rename_ext"    => "Нельзя использовать данное расширение",
		"access_path"          => "Доступ к данной директории запрещён",
		"access_file"          => "Файл не поддерживается",
		"upload"               => "Файл загружен",
		"not_upload"           => "Файл не загружен",
		"file_exists"          => "Файл существует",
		"not_found"            => "Файл не существует",
		"same_name"            => "Имена файлов одинаковые"
	);
	// Автоматическое удаление
	private $autodelete = true;
	// Кол-во лет
	private $year = 5;
	// Корень сайта
	private $base_path = "";
	// Разрешённые директории
	// food, food-individual, etc..
	private $access_path = [];

	/**
	 * В конструкторе применяем переменные
	 * Создаём директории
	 */
	public function __construct(string $base_path, array $params, array $lang) {
		$base_path = rtrim(str_replace('\\', '/', $base_path), "/") . "/";
		$this->params = array_merge($this->params, $params);
		// Удаляем повторение. Переведём в нижний регистр значения массива
		$this->params["access_path"] = array_unique(array_map('strtolower', $this->params["access_path"]));
		// Проверяем присутствие food
		if(!in_array("food", $this->params["access_path"])):
			// Если нет - добавляем в начало
			array_unshift($this->params["access_path"], "food");
		endif;
		/**
		 * Подключение языкового пакета (массив по принципу Evolution CMS)
		 */
		$this->lang = array_merge($this->lang, $lang);
		$this->base_path = $base_path;
		$this->access_path = array();
		$this->autodelete = (bool) $this->params["autodelete"];
		$this->year = (int) $this->params["year"];
		// Проверка директорий
		foreach ($this->params["access_path"] as $key => $path):
			$path = self::TranslitFile($path);
			$dir = $this->pathJoin($this->base_path, $path);
			// Создание директории
			if(!is_dir($dir)):
				// Создаём директорию
				@mkdir($dir, 0755, true);
				// Приеняем права
				@chmod($dir, 0755);
			endif;
			// Автоудаление файлов
			if($this->autodelete && $this->year > 0):
				// Задаём директорию
				$this->path = $path;
				// Удаляем файлы в директории
				$this->DeleteOldFiles();
			endif;
			$this->access_path[] = $path;
		endforeach;
		$this->path = in_array((string) $params["path"], $this->access_path) ? (string) $params["path"] : "";
		$this->output["path"] = $this->path;
	}

	/**
	 *  Удаление файла
	 */
	public function deleteFile(string $file) {
		if($this->checkedPath($this->path)):
			$old_file = $this->pathJoin($this->base_path, $this->path, $file);
			if(@is_file($old_file)):
				// Проверяем на разрешение
				$ext = strtolower(pathinfo($old_file, PATHINFO_EXTENSION));
				if(in_array($ext, self::EXT_FILES)):
					if(@unlink($old_file)):
						// Файл удалён
						$this->output["message"]["success"][] = "<strong>" . $this->lang["delete"] . ":</strong> <code>" . $this->pathJoin($this->path, $file) . "</code>";
					else:
						// Файл не удалён
						$this->output["message"]["error"][] = "<strong>" . $this->lang["not_delete"] . ":</strong> <code>" . $this->pathJoin($this->path, $file) . "</code>";
					endif;
				else:
					// Файл удалить нельзя
					$this->output["message"]["error"][] = "<strong>" . $this->lang["not_file_delete"] . ":</strong> <code>" . $this->pathJoin($this->path, $file) . "</code>";
				endif;
			else:
				// Файл не найден
				$this->output["message"]["error"][] = "<strong>" . $this->lang["not_found"] . ":</strong> <code>" . $this->pathJoin($this->path, $file) . "</code>";
			endif;
		endif;
		return $this;
	}

	public function renameFile($file="", $new_file=""){
		// Проверяем $path. Если не разрешённый, то выходим
		if(!$this->checkedPath($this->path)):
			$this->output["message"]["error"][] = "<strong>" . $this->lang["access_path"] . ":</strong> <code>" . $this->pathJoin($this->path, $file) . " -> " . $this->pathJoin($this->path, $new_file) . "</code>";
			return $this;
		endif;
		// Если имена одинаковые - ничего не делаем. Выходим
		if($file == $new_file):
			if(is_file($this->pathJoin($this->base_path, $this->path, $file))):
				$this->output["message"]["error"][] = "<strong>" . $this->lang["same_name"] . ":</strong> <code>" . $this->pathJoin($this->path, $file) . " -> " . $this->pathJoin($this->path, $new_file) . "</code>";
			else:
				$this->output["message"]["error"][] = "<strong>" . $this->lang["not_found"] . ":</strong> <code>" . $this->pathJoin($this->path, $file) . "</code>";
			endif;
			return $this;
		endif;
		// Исходный файл
		$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		// Переименование только pdf или xlsx
		if(!in_array($ext, self::EXT_FILES)):
			$this->output["message"]["error"][] = "<strong>" . $this->lang["access_rename"] . ":</strong> <code>" . $this->pathJoin($this->path, $file) . "</code>";
			return $this;
		endif;

		$ext_new = strtolower(pathinfo($new_file, PATHINFO_EXTENSION));

		if($ext == $ext_new):
			// Далее
			$new_name = $this->TranslitFile(strtolower(pathinfo($new_file, PATHINFO_FILENAME))) . "." . $ext;
			if($new_name == strtolower(pathinfo($file, PATHINFO_BASENAME))):
				// Имена сходятся (равны)
				$this->output["message"]["error"][] = "<strong>" . $this->lang["access_rename"] . ":</strong> <code>" . $this->pathJoin($this->path, $file) . " -> " . $this->pathJoin($this->path, $new_name) . "</code>";
			else:
				// Переименование
				$fromFile = $this->pathJoin($this->base_path, $this->path, $file);
				$toFile = $this->pathJoin($this->base_path, $this->path, $new_name);
				// Существование исходного файла
				if( is_file( $fromFile )):
					// Не существование нового файла
					if(!is_file( $toFile )):
						// Файла с новым именем нет. Переименовываем.
						if( @rename($fromFile, $toFile) ):
							// Переименовать удалось
							$this->output["message"]["success"][] = "<strong>" . $this->lang["rename"] . ":</strong> <code>" . $this->pathJoin($this->path, $file) . " -> " . $this->pathJoin($this->path, $new_name) . "</code>";
						else:
							// Переименовать не удалось. Ошибка.
							// Возможна при несоблюдении прав на файл.
							$this->output["message"]["error"][] = "<strong>" . $this->lang["not_rename"] . ":</strong> <code>" . $this->pathJoin($this->path, $file) . " -> " . $this->pathJoin($this->path, $new_name) . "</code>";
						endif;
					else:
						// Файла с новым именем существует.
						$this->output["message"]["error"][] = "<strong>" . $this->lang["file_exists"] . ":</strong> <code>" . $this->pathJoin($this->path, $file) . " -> " . $this->pathJoin($this->path, $new_name) . "</code>";
					endif;
				else:
					// Исходный не существует
					$this->output["message"]["error"][] = "<strong>" . $this->lang["not_found"] . ":</strong> <code>" . $this->pathJoin($this->path, $file) . " -> " . $this->pathpathJoin_join($this->path, $new_name) . "</code>";
				endif;
			endif;
		else:
			// Нельзя переименовать с другим расширеним
			$this->output["message"]["error"][] = "<strong>" . $this->lang["access_rename_ext"] . ":</strong> <code>" . $ext . " -> " . $ext_new . "</code>";
		endif;
		return $this;
	}

	/**
	 * Загрузка файлов
	 */
	public function uploadFiles() {
		// Проверяем $path. Если не разрешённый, то выходим
		if($this->path && !$this->checkedPath($this->path)):
			$this->output["message"]["error"][] = "<strong>" . $this->lang["access_path"] . ":</strong> <code>" . $this->path . "</code>";
			$this->path = "";
			// Посути должен быть редирект. Обдумать
			// Если есть загрузка.
			// В Joomla редирект есть. Короче смотреть по реализации для CMS.
			// В классе делать не красиво и не нужно.
			return $this;
		endif;
		// В корень загружать нельзя
		if($this->path == ""):
			return $this;
		endif;
		$path = $this->pathJoin($this->base_path, $this->path);
		// Обрабатываем
		if(isset($_FILES['userfiles'])):
			foreach ($_FILES['userfiles']['name'] as $i => $name):
				if (empty($_FILES['userfiles']['tmp_name'][$i])) continue;
				$name = strtolower($this->TranslitFile($name));
				$extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
				$tmp_name = $_FILES['userfiles']['tmp_name'][$i];
				if(in_array($extension, self::EXT_FILES)):
					// Файл поддерживается
					if(@move_uploaded_file($tmp_name, $this->pathJoin($path, $name))):
						if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'):
							@chmod($this->pathJoin($path, $name), 0644);
						endif;
						// Файл загружен
						$this->output["message"]["success"][] = "<strong>" . $this->lang["upload"] . ":</strong> <code>" . $this->pathJoin($this->path, $name) . "</code>";
					else:
						// Файл не загружен
						$this->output["message"]["error"][] = "<strong>" . $this->lang["not_upload"] . ":</strong> <code>" . $this->pathJoin($this->path, $name) . "</code>";
					endif;
				else:
					// Файл не поддерживается
					$this->output["message"]["error"][] = "<strong>" . $this->lang["access_file"] . ":</strong> <code>" . $this->pathJoin($this->path, $name) . "</code>";
				endif;
			endforeach;
		endif;
		return $this;
	}

	/**
	 * Получение данных директории
	 */
	public function getData() {
		$this->output["access_path"] = $this->access_path;
		$this->output["directory"] = array();
		$this->output["files"]     = array();
		$this->output["path"]      = $this->path;
		// Если существует и разрешён
		if($this->checkedPath($this->path)):
			$dir = $this->pathJoin($this->base_path, $this->path);
			if(is_dir($dir) && is_readable($dir)):
				// Автоудаление файлов
				if($this->autodelete && $this->year > 0):
					$this->DeleteOldFiles($this->path);
				endif;
				// Получаем файлы
				$directory = new \DirectoryIterator($dir);
				foreach ($directory as $fileinfo):
					if (!$fileinfo->isDot()):
						if(!$fileinfo->isDir()):
							$ext = strtolower($fileinfo->getExtension());
							if(in_array($ext, self::EXT_FILES)):
								// Собрать данные о файле.
								/**
								 * Имя
								 * Права
								 * Дата
								 * Размер
								 */
								$name = $fileinfo->getFilename();
								$perms = substr(sprintf('%o', $fileinfo->getPerms()), -4);
								// пока время так. нужно подстроить под настройки времени CMS
								$time = date("d-m-Y H:i:s", $fileinfo->getMTime());
								$size = self::FileSizeConvert($fileinfo->getSize());
								$this->output["files"][$name] = array(
									"icon"  => "food-icon-file-$ext",
									"link"  => $this->pathJoin("", $this->path, $name),
									"name"  => $name,
									"perms" => $perms,
									"time"	=> $time,
									"size"	=> $size
								);
							endif;
						endif;
					endif;
				endforeach;
			else:
				// Создаём директорию
				@mkdir($dir, 0755, true);
				// Приеняем права
				@chmod($dir, 0755);
			endif;
		else:
			// Перечисляем директории
			$this->output["directory"] = $this->access_path;
		endif;
		// Сортировка директорий
		sort($this->output["directory"]);
		// Сортировка файлов
		krsort($this->output["files"]);
		return $this;
	}

	/**
	 * Удаление старых файлов
	 */
	private function DeleteOldFiles() {
		$dir = $this->pathJoin($this->base_path, $this->path);
		$directory = new \DirectoryIterator($dir);
		foreach ($directory as $fileinfo):
			if (!$fileinfo->isDot()):
				if(!$fileinfo->isDir()):
					// Перечисляем только нужные файлы (pdf, xlsx)
					$ext = strtolower($fileinfo->getExtension());
					if(in_array($ext, self::EXT_FILES)):
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
							if($year - $file_year > $this->year && $this->autodelete):
								// Удаляем файл
								$this->deleteFile($name);
							endif;
						endif;
					endif;
				endif;
			endif;
		endforeach;
		return $this;
	}

	/**
	 * Составление пути
	 */
	public function pathJoin(...$base) {
		$result = [];
		foreach ($base as $n):
			$result[] = rtrim( $n, '/' );
		endforeach;
		$path = rtrim(implode('/', $result), '/' );
		return $path;
	}

	/**
	 * Получаем имя директории
	 */
	private function getDirName(string $path) {
		$path = is_string($path) ? str_replace('\\', '/', trim($path)) : "";
		$path = rtrim($path, '/');
		$path = str_replace($this->base_path, '', $path);
		return $path;
	}

	/**
	 * Соответствует ли директория к правилам просмотра
	 */
	private function checkedPath(string $path) {
		$path = is_string($path) ? str_replace('\\', '/', trim($path)) : "";
		if($path == $this->base_path):
			return true;
		endif;
		$path = $this->getDirName($path);
		return in_array($path, $this->access_path);
	}

	/**
	 * Размер файла
	 */
	private static function FileSizeConvert($bytes)
	{
		$bytes = floatval($bytes);
			$arBytes = array(
				array(
					"UNIT" => "TB",
					"VALUE" => pow(1024, 4)
				),
				array(
					"UNIT" => "GB",
					"VALUE" => pow(1024, 3)
				),
				array(
					"UNIT" => "MB",
					"VALUE" => pow(1024, 2)
				),
				array(
					"UNIT" => "KB",
					"VALUE" => 1024
				),
				array(
					"UNIT" => "B",
					"VALUE" => 1
				),
			);

		foreach($arBytes as $arItem):
			if($bytes >= $arItem["VALUE"]):
				$result = $bytes / $arItem["VALUE"];
				$result = strval(round($result, 2)) . " " . $arItem["UNIT"];
				break;
			endif;
		endforeach;
		return $result;
	}

	/**
	 * Очистка имени файла от лишних символов
	 */
	private static function StripFileName($filename = "") {
		$filename = strip_tags($filename);
		$filename = preg_replace('/[^\.A-Za-z0-9 _-]/', '', $filename);
		$filename = preg_replace('/\s+/', '-', $filename);
		$filename = preg_replace('/_+/', '_', $filename);
		$filename = preg_replace('/-+/', '-', $filename);
		$filename = trim($filename, '-_.');
		return $filename;
	}

	/**
	 * Транслит имени файла
	 */
	public static function TranslitFile($filename){
		$converter = array(
			'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
			'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
			'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
			'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
			'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
			'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
			'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
	 
			'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
			'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
			'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
			'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
			'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
			'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
			'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',
		);
		$filename = str_replace(array(' ', ','), '-', $filename);
		$filename = strtr($filename, $converter);
		$filename = self::StripFileName($filename);
		$filename = strtolower($filename);
		return $filename;
	}

}
