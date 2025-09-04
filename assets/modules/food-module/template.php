<?php
if( ! defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true) {
	http_response_code(403);
	exit();
}

$modPath = str_replace(MODX_BASE_PATH, '', $base_path);
$upload_maxsize = $modx->config['upload_maxsize'];
?>
<?php
// Подключаем main.css
$css = MODX_BASE_PATH . $modPath . 'css/main.min.css';
$lcss_time = filemtime($css);
?>
<link type="text/css" rel="stylesheet" href="/<?= $modPath;?>css/main.min.css?<?= $lcss_time;?>">

<style type="text/css">
	.evo-popup-close.close {
		cursor: pointer;
	}
	.alert:empty {
		display: none;
	}
</style>
<script>
	const FOOD_FILE_PATH = "<?= checkedPath($startpath, $access_path) ? $title_path : ""; ?>";
	const FOOD_MOD_PATH = "/<?= $modPath;?>";
</script>
<div class="container">
	<h1 class="text-left"><i class="fa fa-folder-open"></i><?= $_lang['sch_title']; ?></h1>
	<h2 class="text-left" style="font-weight: 700;"><?= $title;?></h2>
	<div id="actions" style="display: none;"><div class="btn-group"></div></div>
	<div id="ManageFiles">
		<div class="container breadcrumbs">
			<i class="fa fa-folder-open-o FilesTopFolder"></i>
			<a href="?a=112&id=<?= $module["id"];?>"><?= $_lang["sch_food_top"];?></a>
<?php
	if(checkedPath($startpath, $access_path)):
?>
			<span class="link-dir"><a href="?a=112&id=<?= $module["id"];?>&mode=dir&path=<?= $title_path; ?>"><?= $title_path; ?></a></span>
<?php
	endif;
?>
		</div>
		<div class="alert alert-danger" role="alert"><?= $all['error'];?></div>
		<div class="alert alert-success" role="alert"><?= $all['success'];?></div>
<?php
	// Форма загрузки
	if (((@ini_get("file_uploads") == true) || get_cfg_var("file_uploads") == 1) && is_writable($startpath) && checkedPath($startpath, $access_path)):
?>
		<form class="text-right" name="upload" method="post" action="?a=112&id=<?= $module['id']; ?>&mode=dir&path=<?= $title_path; ?>" enctype="multipart/form-data" style="display: none;">
			<input type="hidden" name="MAX_FILE_SIZE" value="<?= isset($upload_maxsize) ? $upload_maxsize : 3145728 ?>">
			<input type="hidden" name="mode" value="upload">
			<div id="uploader" class="text-right">
				<label class="btn btn-secondary text-uppercase" style="display: none;">
					<input type="file" name="userfiles[]" onchange="uploadFiles(this);" multiple accept=".xlsx,.pdf">
				</label>
				<p id="p_uploads" class="alert alert-info"></p>
				<a class="btn btn-success text-uppercase" href="javascript:;" onclick="document.upload.submit()"><i class="<?= $_style['files_upload'];?>"></i> <?= $_lang['files_uploadfile']; ?></a>
			</div>
		</form>
<?php
	endif;
	if(checkedPath($startpath, $access_path)):
?>
		<form name="modifed" method="post" action="?a=112&id=<?= $module['id']; ?>&mode=dir&path=<?= $title_path; ?>" enctype="multipart/form-data">
			<input type="hidden" name="mode" value="">
			<input type="hidden" name="path" value="<?= $title_path; ?>/">
			<input type="hidden" name="file" value="">
			<input type="hidden" name="newfile" value="">
		</form>
<?php
	endif;
?>
		<div class="table-wrapper">
			<table id="table" class="table data table-bordered">
				<thead>
					<tr>
						<th><?= $_lang['files_filename']; ?></th>
						<th style="width: 1%;" class="text-nowrap"><?= $_lang['sch_permission'] ?></th>
						<th style="width: 1%;" class="text-nowrap"><?= $_lang['files_modified']; ?></th>
<?php
					// Для файлов
					if(checkedPath($startpath, $access_path)):
?>
						<th style="width: 1%;" class="text-nowrap"><?= $_lang['files_filesize']; ?></th>
						<th style="width: 1%;" class="text-nowrap"><?= $_lang['files_fileoptions'] ?></th>
<?php
					endif;
?>
					</tr>
				</thead>
				<tbody>
<?php
if(!checkedPath($startpath, $access_path)):
foreach($directorys as $dir):
	$f = MODX_BASE_PATH . $dir;
    $size = dir_size($f);
	$ltime = filemtime($f);
	$perms = substr(sprintf('%o', fileperms($f)), -4);
?>
					<tr>
						<td>
							<i class="<?= $_style['actions_folder'];?>"></i> <a href="?a=112&id=<?= $module["id"];?>&mode=dir&path=<?= $dir;?>"><?= $dir;?></a>
						</td>
						<td class="text-right text-nowrap"><?= $perms;?></td>
						<td class="text-right text-nowrap"><?= $modx->toDateFormat($ltime);?></td>
					</tr>
<?php
endforeach;
endif;
if(checkedPath($startpath, $access_path)):
if($files):
	foreach($files as $file):
		$tmp_file = $startpath . "/" . $file;
		$stat = 0;
		$ltime = 0;
		if(is_file($tmp_file)):
			$ltime = filemtime($tmp_file);
			$stat = filesize($tmp_file);
			$perms = substr(sprintf('%o', fileperms($tmp_file)), -4);
?>
					<tr>
						<td class="text-nowrap"><?php if(is_file(MODX_BASE_PATH . "viewer/jquery.min.js") && is_file(MODX_BASE_PATH . 'viewer/fancybox.min.js')):
?><a data-file="<?= $file_path . "/" . $file;?>" href="<?= $file_path . '/' . $file;?>" title="<?= $_lang['files_viewfile'];?>:
<?= $file;?>"><?= $file;?></a><?php else: ?><a href="<?= $file_path . '/' . $file;?>" title="<?= $_lang['file_download_file'];?>:
<?= $file;?>" download><?= $file;?></a><?php endif; ?></td>
						<td class="text-right text-nowrap"><?= $perms;?></td>
						<td class="text-right text-nowrap"><?= $modx->toDateFormat($ltime);?></td>
						<td class="text-right text-nowrap"><?= $modx->nicesize($stat);?></td>
						<td class="actions text-right"><?php
						if(is_file(MODX_BASE_PATH . "viewer/jquery.min.js") && is_file(MODX_BASE_PATH . 'viewer/fancybox.min.js')): ?><a data-file="<?= $file_path . "/" . $file;?>" href="<?= $file_path . '/' . $file;?>" title="<?= $_lang['files_viewfile'];?>:
<?= $file;?>"><i class="<?= $_style['files_view'];?>"></i></a><?php
						else: ?><a href="<?= $file_path . '/' . $file;?>" title="<?= $_lang['file_download_file'];?>:
<?= $file;?>" download><i class="<?= $_style['files_download'];?>"></i></a><?php
						endif;?><a href="<?= $file_path . '/' . $file;?>" title="<?= $_lang['rename'];?>:
<?= $file;?>" data-mod="<?= $file;?>" data-mode="rename" data-newfile="<?= $file;?>"><i class="<?= $_style['files_rename'];?>"></i></a><a href="<?= $file_path . '/' . $file;?>" title="<?= $_lang['file_delete_file'];?>:
<?= $file;?>" data-mod="<?= $file;?>" data-mode="delete"><i class="<?= $_style['files_delete'];?>"></i></a></td>
					</tr>
<?php
		endif;
	endforeach;
endif;
endif;
?>
				</tbody>
			</table>
		</div>
	</div>
	<p class="developer_food text-right"><?= $_lang["sch_git_help"];?> <a href="https://github.com/ProjectSoft-STUDIONIONS/food-module/issues" target="_blank">https://github.com/ProjectSoft-STUDIONIONS/food-module/issues</a><br>Telegram: <a href="https://t.me/ProjectSoft" target="_blank">https://t.me/ProjectSoft</a></p>
</div>
<?php
// Данных файлов может и не быть
if(is_file(MODX_BASE_PATH . "viewer/app.min.css")):
?>
<link type="text/css" rel="stylesheet" href="/viewer/app.min.css"></link>
<?php
endif;
// Данных файлов может и не быть
if(is_file(MODX_BASE_PATH . "viewer/jquery.min.js")):
?>
<script src="/viewer/jquery.min.js"></script>
<?php
endif;
// Данных файлов может и не быть
if(is_file(MODX_BASE_PATH . 'viewer/fancybox.min.js')):
?>
<script src="/viewer/fancybox.min.js"></script>
<?php
endif;

// Подключаем DataTables только внутри директории
if(checkedPath($startpath, $access_path)):
	$jsDT = MODX_BASE_PATH . $modPath . 'js/app.min.js';
	$jsDT_time = filemtime($jsDT);
?>
	<script src="/<?= $modPath;?>js/app.min.js?<?= $jsDT_time;?>"></script>
<?php
endif;

// Подключаем main.js
$js = MODX_BASE_PATH . $modPath . 'js/main.min.js';
$ljs_time = filemtime($js);
?>
<script src="/<?= $modPath;?>js/main.min.js?<?= $ljs_time;?>"></script>
