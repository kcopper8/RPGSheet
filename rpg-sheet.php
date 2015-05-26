<?php
/*
Plugin Name: RPG Sheet
Plugin URI: https://github.com/kcopper8/RPGSheet
Description: Add RPG Sheet
Author: kcopper8
Version: 0.1
Author URI: https://github.com/kcopper8
License: MIT
*/



add_action('init', 'rs_create_post_type');
function rs_create_post_type() {
	register_post_type('rs_sheet', 
		array(
			'labels' => array(
				'name' => __('Sheets'),
				'singular_name' => __('Sheet')
			),
			'public' => true
			// 'has_archive' => true
		)
	);
}


define('RS_PLUGIN_ROOT', plugin_dir_path(__FILE__));
define('RS_PLUGIN_ROOT_URL', plugin_dir_url(__FILE__));
define('RS_PLUGIN_SHEETS_PATH', RS_PLUGIN_ROOT . '/sheets');
define('RS_PLUGIN_SHEETS_URL', RS_PLUGIN_ROOT_URL . '/sheets');

define('RS_META_NAME_SHEET_DATA', 'rs_data');
define('RS_META_NAME_SHEET_TYPE', 'rs_type');



/* Filter the single_template with our custom function*/

add_filter('single_template', 'rs_custom_template');

function rs_custom_template($single) {
    global $wp_query, $post;

// Checks for single template by post type //
if ($post->post_type == "rs_sheet"){
    if(file_exists(plugin_dir_path(__FILE__). 'templates/single-rs_sheet.php')) {
    	wp_enqueue_script('rs_sheet_meta_script', RS_PLUGIN_ROOT_URL . "/scripts/scripts.js", array('jquery', 'underscore'));
        return plugin_dir_path(__FILE__). 'templates/single-rs_sheet.php';
    }
}
    return $single;
}


add_action('add_meta_boxes_rs_sheet', 'rs_meta_box_add_function');
function rs_meta_box_add_function() {
	wp_enqueue_script('rs_sheet_meta_script', RS_PLUGIN_ROOT_URL . "/scripts/scripts.js", array('jquery'));
	add_meta_box('rs_sheet', '시트', 'rs_add_meta_box', 'rs_sheet');

}


function rs_common_get_allsheets() {
	$raw_dirs = scandir(RS_PLUGIN_SHEETS_PATH);
	$sheets = array();

	foreach ($raw_dirs as $dir) {
		if ($dir == '.' || $dir == '..') {
			continue;
		}

		$sheets[] = $dir;

	}

	return $sheets;
}

function rs_common_get_sheet_data($rs_type) {
	$sheetJsonFilePath = RS_PLUGIN_SHEETS_PATH . "/$rs_type/sheet.json";
	$sheet_data = json_decode(file_get_contents($sheetJsonFilePath));
	// return array("html" => "fate.html", "css" => "fate.css");
	return $sheet_data;
}

function rs_common_get_post_rs_type() {
	$rs_type = get_post_custom_values(RS_META_NAME_SHEET_TYPE);
	return (count($rs_type) > 0) ? $rs_type[0] : "";
}


function rs_add_meta_box($post) {
	include(plugin_dir_path(__FILE__) . 'metabox/rs-metabox.php');
}


add_action('save_post', 'rs_save_meta_box');

function rs_save_meta_box($post_id) {
    global $wp_query, $post;

	if ($post -> post_type != 'rs_sheet') {
		return;
	}


	// print($_REQUEST['rs_data']);
	// print(wp_unslash($_REQUEST['rs_data']));
	// wp_die('www' . ' ' . ($post -> post_type) . ' '. $post_id . ' '. esc_attr($_REQUEST['rs_data']));

	update_post_meta($post_id, RS_META_NAME_SHEET_DATA, esc_attr($_REQUEST[RS_META_NAME_SHEET_DATA]));
	update_post_meta($post_id, RS_META_NAME_SHEET_TYPE, esc_attr($_REQUEST[RS_META_NAME_SHEET_TYPE]));
}

?>