<?php
if( ! defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true) {
	http_response_code(403);
	exit();
}

$modPath = str_replace(MODX_BASE_PATH, '', $base_path);
$upload_maxsize = $modx->config['upload_maxsize'];
$mod = getModule();
?>
<script>
	const FOOD_FILE_PATH = "<?= $data["path"]; ?>";
	const FOOD_MOD_PATH = "<?= FOOD_MOD_PATH; ?>";
	const FOOD_LANG_FILE = "<?= $langJson; ?>";
	const LANG = <?= $lng; ?>;
</script>
<div id="food-module-evo">
	<div class="container">
		<div class="food-title-flex">
			<h1 class="text-left"><i class="fa fa-folder-open"></i><?= $_lang['sch_title']; ?></h1>
<?php
		if($mod && $modx->hasPermission('edit_module')):
?>
			<div class="food-settings">
				<a class="btn btn-primary food-icon food-icon-tools" href="index.php?a=108&id=<?= $mod["id"];?>" title="<?= $_lang["sch_settings"]; ?>" target="main"></a>
			</div>
<?php
		endif;
?>
		</div>
<?php
		if($data["path"]):
?>
		<h2 class="text-left"><?= $_lang["sch_directory"]; ?> <code>/<?=$data["path"]; ?>/</code> <a class="food-icon food-icon-new-window" href="/<?= $data["path"]; ?>/" target="_blank"></a></h2>
<?php
		else:
?>
		<h2 class="text-left"><?= $_lang["sch_directorys"]; ?></h2>
<?php
		endif;
?>
		<div id="actions" style="display: none;"><div class="btn-group"></div></div>
		<div id="ManageFiles">
			<div class="breadcrumbs">
				<i class="food-icon food-icon-folder-open-o FilesTopFolder"></i>
				<a href="?a=112&id=<?= $module["id"];?>"><?= $_lang["sch_food_top"];?></a>
<?php
		if($data["path"]):
?>
				<span class="link-dir"><a href="?a=112&id=<?= $module["id"];?>&path=<?= $data["path"]; ?>"><?= $data["path"]; ?></a></span>
<?php
		endif;
?>
			</div>
<?php
		$style_success = $data["message"]["success"] ? "" : ' style="display: none;"';
		$style_error = $data["message"]["error"] ? "" : ' style="display: none;"';
		$str_success = $data["message"]["success"] ? "<div>" . implode("</div><div>", $data["message"]["success"]) . "</div>" : "";
		$str_error = $data["message"]["success"] ? "<div>" . implode("</div><div>", $data["message"]["error"]) . "</div>" : "";
?>
			<div class="alert alert-danger alert-icon-close" role="alert"<?= $style_error;?>><?= $str_error; ?><i class="icon-close">×</i></div>
			<div class="alert alert-success alert-icon-close" role="alert"<?= $style_success;?>><?= $str_success; ?><i class="icon-close">×</i></div>
<?php
		// Если $data["path"]
		if($data["path"]):
?>
			<p id="p_uploads" class="alert alert-info"></p>
			<form class="text-right" name="upload" method="post" action="?a=112&id=<?= $module['id']; ?>&path=<?= $data["path"]; ?>" enctype="multipart/form-data" style="display: none;">
				<input type="hidden" name="MAX_FILE_SIZE" value="<?= isset($upload_maxsize) ? $upload_maxsize : 3145728 ?>">
				<input type="hidden" name="mode" value="upload">
				<div id="uploader" class="text-right" style="display: none !important;">
					<input type="file" name="userfiles[]" onchange="uploadFiles(this);" multiple accept=".xlsx,.pdf" max="20">
				</div>
			</form>
			<form name="modifed" method="post" action="?a=112&id=<?= $module['id']; ?>&path=<?= $data["path"]; ?>" enctype="multipart/form-data">
				<input type="hidden" name="mode" value="">
				<input type="hidden" name="path" value="<?= $data["path"]; ?>/">
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
<?php
					if($data["path"] && $data["files"]):
?>
							<th><?= $_lang['files_filename']; ?></th>
							<th style="width: 1%;" class="text-nowrap"><?= $_lang['sch_permission'] ?></th>
							<th style="width: 1%;" class="text-nowrap"><?= $_lang['files_modified']; ?></th>
							<th style="width: 1%;" class="text-nowrap"><?= $_lang['files_filesize']; ?></th>
							<th style="width: 1%;" class="text-nowrap"><?= $_lang['sch_actions'] ?></th>
<?php
					elseif(!$data["path"] && $data["directory"]):
?>
							<th width="100%"><?= $_lang["sch_th_directorys"]; ?></th>
							<th width="auto"></th>
<?php
					endif;
?>
						</tr>
					</thead>
					<tbody>
<?php
					if($data["path"] && $data["files"]):
						foreach ($data["files"] as $value):
?>
						<tr>
							<td class="text-nowrap"><i class="food-icon <?= $value["icon"] ;?>"></i><?php if(is_file(MODX_BASE_PATH . "viewer/jquery.min.js") && is_file(MODX_BASE_PATH . 'viewer/fancybox.min.js')):
	?><a data-file="<?= $value["link"];?>" href="<?= $value["link"];?>" title="<?= $_lang['files_viewfile'];?>:
	<?= $value["name"];?>"><?= $value["name"];?></a><?php else: ?><a href="<?= $value["link"];?>" title="<?= $_lang['file_download_file'];?>:
	<?= $value["name"];?>" download><?= $value["name"];?></a><?php endif; ?></td>
							<td class="text-right text-nowrap"><?= $value["perms"];?></td>
							<td class="text-right text-nowrap"><?= $value["time"];?></td>
							<td class="text-right text-nowrap"><?= $value["size"];?></td>
							<td class="actions text-center">
								<button class="food-icon-edit btn" title="<?= $_lang['rename'];?>:
	<?= $value["name"];?>" data-mod="<?= $value["name"];?>" data-mode="rename" data-newfile="<?= $value["name"];?>"></button>
								<button class="food-icon-trash btn btn-danger" title="<?= $_lang['file_delete_file'];?>:
	<?= $value["name"];?>" data-mod="<?= $value["name"];?>" data-mode="delete"></button>
							</td>
						</tr>
<?php
						endforeach;
					elseif(!$data["path"] && $data["directory"]):
						foreach ( $data["directory"] as $value ):
?>
						<tr>
							<td>
								<i class="food-icon food-icon-folder-open-o"></i> <a href="?a=112&id=<?= $module["id"];?>&path=<?= $value;?>"><?= $value;?></a>
							</td>
							<td class="text-right text-nowrap"><a href="/<?= $value;?>/" target="_blank" class="food-icon food-icon-new-window"></a></td>
						</tr>
<?php
						endforeach;
					endif;
?>
					</tbody>
				</table>
			</div>
		</div>
		<p class="developer_food text-right"><?= $_lang["sch_git_help"];?> <a href="https://github.com/ProjectSoft-STUDIONIONS/food-module/issues" target="_blank">https://github.com/ProjectSoft-STUDIONIONS/food-module/issues</a><br>Telegram: <a href="https://t.me/ProjectSoft" target="_blank">https://t.me/ProjectSoft</a></p>
	</div>
</div>
<?php
// Подключаем DataTables только внутри директории
/* <div><pre><code><?= print_r($data, true);?></code></pre></div> */
if($data["path"]):
	$jsDT = MODX_BASE_PATH . ltrim(FOOD_MOD_PATH, '/') . 'js/app.min.js';
	$jsDT_time = filemtime($jsDT);
	// Подключаем main.js
	$js = MODX_BASE_PATH . ltrim(FOOD_MOD_PATH, '/') . 'js/main.min.js';
	$ljs_time = filemtime($js);
?>
<script src="/viewer/jquery.min.js"></script>
<script src="/viewer/fancybox.min.js"></script>
<script src="<?= FOOD_MOD_PATH;?>js/app.min.js?<?= $jsDT_time;?>"></script>
<script src="<?= FOOD_MOD_PATH;?>js/main.min.js?<?= $ljs_time;?>"></script>
<?php
endif;
?>
