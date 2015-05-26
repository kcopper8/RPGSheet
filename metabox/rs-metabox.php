<label for="rs_sheet_type_select">시트 종류</label>
<select name="<?=RS_META_NAME_SHEET_TYPE?>" id="rs_sheet_type_select">
<?php
	$rs_type = rs_common_get_post_rs_type();
	$sheets = rs_common_get_allsheets();

	foreach ($sheets as $value) {
		if ($rs_type == $value) {
			$selected = ' selected="selected"';
		} else {
			$selected = '';
		}

		print("<option value=\"$value\"$selected>$value</option>");
	}


	$rs_sheet_data = rs_common_get_sheet_data($rs_type);
	$rs_sheet_css_url = RS_PLUGIN_SHEETS_URL . "/$rs_type/" . $rs_sheet_data->css;
	$rs_sheet_html_path = RS_PLUGIN_SHEETS_PATH . "/$rs_type/" . $rs_sheet_data->html;
?>
</select>
<link rel="stylesheet" type="text/css" href="<?=RS_PLUGIN_ROOT_URL?>styles/style.css">
<link rel="stylesheet" type="text/css" href="<?=$rs_sheet_css_url?>">
<input type="hidden" name="rs_data">
<script type="text/javascript">
	window.rsData = <?php
		$rs_data = get_post_custom_values("rs_data");
		echo(html_entity_decode($rs_data[0]));
	?>;
</script>
<?php
include($rs_sheet_html_path);
?>