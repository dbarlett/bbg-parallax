<?php // load parent theme CSS

error_reporting(E_ALL);ini_set('display_errors', 1);
//define('WP_DEBUG', true);

function load_parent_theme_css() {
    wp_enqueue_style( 'parent-theme', get_template_directory_uri() . '/style.css' );
}

add_action( 'wp_enqueue_scripts', 'load_parent_theme_css' );


function custom_enqueue_scripts() { wp_deregister_style( 'themify-media-queries' ); wp_enqueue_style( 'themify-media-queries', get_stylesheet_directory_uri() . '/media-queries.css' ); } add_action( 'wp_enqueue_scripts', 'custom_enqueue_scripts', 12 );


add_action("gform_after_submission_3", "acf_post_submission", 10, 2);
function acf_post_submission ($entry, $form) {
	$field_key = 'field_54817fefd7196';
	$user_id = 'user_' . get_current_user_id();
	$value = array();
	$value += get_field($field_key, $user_id);
	// fix the date format, no hyphen!
	foreach($value as $key => &$val) {
		$val['date'] = str_replace('-', '', $val['date']);
	}
	$value[] = array(
		'date' => date("Ymd", strtotime($entry[2])),
		'total_nutrition_points_earned' => $entry[6],
		'total_fitness_points_earned' => $entry[7],
		'total_wellness_points_earned' => $entry[8]
	);
	// print_r($value); die;
	error_log(print_r($value,1), 3, "/tmp/my-errors.log");
	update_field($field_key, $value, $user_id);
}
