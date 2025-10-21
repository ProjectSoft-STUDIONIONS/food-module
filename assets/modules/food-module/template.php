<?php
if( ! defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true) {
	http_response_code(403);
	exit();
}

$modPath = str_replace(MODX_BASE_PATH, '', $base_path);
$upload_maxsize = $modx->config['upload_maxsize'];
?>
<script>
	const FOOD_FILE_PATH = "<?= checkedPath($startpath, $access_path) ? $title_path : ""; ?>";
	const FOOD_MOD_PATH = "/<?= $modPath;?>";
</script>
<div id="food-module-evo">
	<div class="container">
		<h1 class="text-left"><i class="fa fa-folder-open"></i><?= $_lang['sch_title']; ?></h1>
		<h2 class="text-left" style="font-weight: 700;"><?= $title;?></h2>
		<div id="actions" style="display: none;"><div class="btn-group"></div></div>
		<div id="ManageFiles">
			<div class="container breadcrumbs">
				<i class="food-icon food-icon-folder-open-o FilesTopFolder"></i>
				<a href="?a=112&id=<?= $module["id"];?>"><?= $_lang["sch_food_top"];?></a>
	<?php
		if(checkedPath($startpath, $access_path)):
	?>
				<span class="link-dir"><a href="?a=112&id=<?= $module["id"];?>&mode=dir&path=<?= $title_path; ?>"><?= $title_path; ?></a></span>
	<?php
		endif;
			$style_error = $all['error'] ? '' : ' style="display: none;"';
			$style_success = $all['success'] ?  '' : ' style="display: none;"';
	?>
			</div>
			<div class="alert alert-danger alert-icon-close" role="alert"<?= $style_error;?>><?= $all['error'];?><i class="icon-close">×</i></div>
			<div class="alert alert-success alert-icon-close" role="alert"<?= $style_success;?>><?= $all['success'];?><i class="icon-close">×</i></div>
	<?php
		// Форма загрузки
		if (((@ini_get("file_uploads") == true) || get_cfg_var("file_uploads") == 1) && is_writable($startpath) && checkedPath($startpath, $access_path)):
	?>
			<p id="p_uploads" class="alert alert-info"></p>
			<form class="text-right" name="upload" method="post" action="?a=112&id=<?= $module['id']; ?>&mode=dir&path=<?= $title_path; ?>" enctype="multipart/form-data" style="display: none;">
				<input type="hidden" name="MAX_FILE_SIZE" value="<?= isset($upload_maxsize) ? $upload_maxsize : 3145728 ?>">
				<input type="hidden" name="mode" value="upload">
				<div id="uploader" class="text-right" style="display: none !important;">
					<input type="file" name="userfiles[]" onchange="uploadFiles(this);" multiple accept=".xlsx,.pdf" max="20">
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
	<?php
						// Для файлов
						if(checkedPath($startpath, $access_path)):
	?>
							<th><?= $_lang['files_filename']; ?></th>
							<th style="width: 1%;" class="text-nowrap"><?= $_lang['sch_permission'] ?></th>
							<th style="width: 1%;" class="text-nowrap"><?= $_lang['files_modified']; ?></th>
							<th style="width: 1%;" class="text-nowrap"><?= $_lang['files_filesize']; ?></th>
							<th style="width: 1%;" class="text-nowrap"><?= $_lang['sch_actions'] ?></th>
	<?php
						endif;
	?>
	<?php
						if(!checkedPath($startpath, $access_path)):
	?>
								<th width="100%"><?= $_lang["sch_directorys"]; ?></th>
								<th width="auto"></th>
	<?php
						endif;
	?>
						</tr>
					</thead>
					<tbody>
	<?php
	if(!checkedPath($startpath, $access_path)):
	foreach($directorys as $dir):
	?>
						<tr>
							<td>
								<i class="food-icon food-icon-folder-open-o"></i> <a href="?a=112&id=<?= $module["id"];?>&mode=dir&path=<?= $dir;?>"><?= $dir;?></a>
							</td>
							<td class="text-right text-nowrap"><a href="/<?= $dir;?>/" target="_blank" class="food-icon food-icon-new-window"></a></td>
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
				$ext = strtolower(pathinfo($tmp_file, PATHINFO_EXTENSION));
	?>
						<tr>
							<td class="text-nowrap"><i class="food-icon-export-<?= $ext;?>"></i><?php if(is_file(MODX_BASE_PATH . "viewer/jquery.min.js") && is_file(MODX_BASE_PATH . 'viewer/fancybox.min.js')):
	?><a data-file="<?= $file_path . "/" . $file;?>" href="<?= $file_path . '/' . $file;?>" title="<?= $_lang['files_viewfile'];?>:
	<?= $file;?>"><?= $file;?></a><?php else: ?><a href="<?= $file_path . '/' . $file;?>" title="<?= $_lang['file_download_file'];?>:
	<?= $file;?>" download><?= $file;?></a><?php endif; ?></td>
							<td class="text-right text-nowrap"><?= $perms;?></td>
							<td class="text-right text-nowrap"><?= $modx->toDateFormat($ltime);?></td>
							<td class="text-right text-nowrap"><?= $modx->nicesize($stat);?></td>
							<td class="actions text-center">
								<button class="food-icon-edit btn" title="<?= $_lang['rename'];?>:
	<?= $file;?>" data-mod="<?= $file;?>" data-mode="rename" data-newfile="<?= $file;?>"></button>
								<button class="food-icon-trash btn btn-danger" title="<?= $_lang['file_delete_file'];?>:
	<?= $file;?>" data-mod="<?= $file;?>" data-mode="delete"></button>
							</td>
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
	</div>
	<p class="developer_food text-right"><?= $_lang["sch_git_help"];?> <a href="https://github.com/ProjectSoft-STUDIONIONS/food-module/issues" target="_blank">https://github.com/ProjectSoft-STUDIONIONS/food-module/issues</a><br>Telegram: <a href="https://t.me/ProjectSoft" target="_blank">https://t.me/ProjectSoft</a></p>
</div>
<?php
// Эти файлы должны быть обязательно.
// Реализовано при установке модуля.
?>
<script src="/viewer/jquery.min.js"></script>
<script src="/viewer/fancybox.min.js"></script>
<?php
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
<?php
/**

<h3>Convert fonts to SVG</h3>
<div class="svgcontainer" id="svgcontainer"></div>
<script src="https://unpkg.com/wawoff2@2.0.1/build/decompress_binding.js"></script>
<script src='https://cdn.jsdelivr.net/npm/opentype.js@latest/dist/opentype.min.js'></script>
<script>
	let fontFile = "https://getbootstrap.com/docs/3.3/dist/fonts/glyphicons-halflings-regular.woff2";

// init
loadFont(fontFile, processFont);

//default
let params = {
  fontSize: 100,
  decimals: 2
};

// process font file after loading and parsing
function processFont(font) {
  showGlyphs(font, params)
}


// create svg sprites from font glyphs
function showGlyphs(font, params) {
  // sanitize font name
  let fontFamily = font.tables.name.fontFamily.en.replaceAll(' ', '_').replaceAll('.', '_');
  let unitsPerEm = font.unitsPerEm;
  let ratio = params.fontSize / unitsPerEm;
  let ascender = font.ascender;
  let descender = Math.abs(font.descender);
  let lineHeight = (ascender + descender) * ratio;
  let baseline = +((100 / (ascender + descender)) * descender).toFixed(3) + 2;

  let decimals = params.decimals;
  let glyphs = font.glyphs.glyphs;
  let keys = Object.keys(glyphs).length;
  let htmlOutput = '';
  let useMarkup = '';

  for (let i = 0; i < keys; i++) {
    let glyph = glyphs[i];
    let lineHeight = (ascender + descender) * ratio;
    let leftSB = glyph.leftSideBearing * ratio;
    let rightSB = (glyph.advanceWidth - glyph.xMax) * ratio;
    let glyphW = (glyph.advanceWidth) * ratio;
    let poxX = 0;

    // adjust negative widths
    if ((glyph.advanceWidth + leftSB) < 0) {
      glyphW = Math.abs(leftSB) + Math.abs(glyph.advanceWidth) + Math.abs(rightSB);
      poxX = Math.abs(leftSB);
    }

    // get svg path data
    let path = glyph.getPath(
      poxX,
      ascender * ratio,
      params.fontSize
    ).toSVG(decimals);


    if (Object.hasOwn(glyph, 'points')) {
      // add symbol definitions
      htmlOutput += `<symbol id="symbol_${glyph.name}" data-id="${glyph.index}" viewBox="0 0 ${+(glyphW).toFixed(2)} ${+(lineHeight).toFixed(2)}"> ${path}</symbol>`;

      // add visible <use> instances
      useMarkup += `<svg id="use_wrap_${glyph.name}"  viewBox="0 0 ${+(glyphW).toFixed(2)} ${+(lineHeight).toFixed(2)}"><use href="#symbol_${glyph.name}" /></svg>`;
    }
  }

  // add hidden svg sprite
  htmlOutput = `<svg xmlns="http://www.w3.org/2000/svg" id="sprite_${fontFamily}" style="width:0; height:0; position:absolute; overflow:hidden;">` + htmlOutput + `</svg>` + useMarkup;

  // render html
  svgcontainer.innerHTML = htmlOutput;

}



 * load font via opentype.js
 * decompress woff2 to truetype using
 * https://github.com/fontello/wawoff2
 * Based on yne's comment:
 * https://github.com/opentypejs/opentype.js/issues/183#issuecomment-1147228025

function loadFont(src, callback) {
  let buffer = {};
  let font = {};
  let ext;

  // is file
  if (src instanceof Object) {
    // get file extension to skip woff2 decompression
    let filename = src[0].name.split(".");
    ext = filename[filename.length - 1];
    buffer = src[0].arrayBuffer();
  }
  // is url
  else {
    let url = src.split(".");
    ext = url[url.length - 1];
    buffer = fetch(src).then((res) => res.arrayBuffer());
  }
  buffer.then((data) => {
    // decompress woff2
    if (ext === "woff2") {
      data = Uint8Array.from(Module.decompress(data)).buffer;
    }
    font = opentype.parse(data);
    callback(font);
  });
}
</script>

*/
