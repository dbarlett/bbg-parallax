<?php
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
//define('WP_DEBUG', true);

// load parent theme CSS
add_action( 'wp_enqueue_scripts', 'load_parent_theme_css' );
function load_parent_theme_css() {
	wp_enqueue_style( 'parent-theme', get_template_directory_uri() . '/style.css' );
}

add_action( 'wp_enqueue_scripts', 'custom_enqueue_scripts', 12 );
function custom_enqueue_scripts() {
	wp_deregister_style( 'themify-media-queries' );
	wp_enqueue_style( 'themify-media-queries', get_stylesheet_directory_uri() . '/media-queries.css' );
}

add_action( 'gform_after_submission_3', 'acf_post_submission', 10, 2 );
function acf_post_submission( $entry, $form ) {
	$field_key = 'field_54817fefd7196';
	$user_id = 'user_' . get_current_user_id();
	$value = array();
	$value += get_field( $field_key, $user_id );
	// fix the date format, no hyphen!
	foreach ( $value as $key => &$val ) {
		$val['date'] = str_replace( '-', '', $val['date'] );
	}
	$value[] = array(
		'date'                          => date( 'Ymd', strtotime( $entry[2] ) ),
		'total_nutrition_points_earned' => $entry[6],
		'total_fitness_points_earned'   => $entry[7],
		'total_wellness_points_earned'  => $entry[8],
	);
	// print_r($value); die;
	error_log( print_r( $value, 1 ), 3, '/tmp/my-errors.log' );
	update_field( $field_key, $value, $user_id );
}

/**
 * Limit entries for form #3
 * http://www.gravityhelp.com/documentation/gravity-forms/extending-gravity-forms/hooks/filters/gform_pre_render/
 */
add_filter( 'gform_pre_render_3', 'bbg_gform_pre_render_3', 10, 1 );
function bbg_gform_pre_render_3( $form ) {
	$current_timestamp = current_time( 'timestamp' );
	$current_hour = current_time( 'H' );
	if ( ( 0 <= $current_hour ) && ( $current_hour <= 14 ) ) {
		$yesterday = $current_timestamp - ( 60 * 60 * 16 );
		$form['description'] = sprintf(
			'Enter points for yesterday (%s)',
			date( 'l n/j', $yesterday )
		);
		foreach ( $form['fields'] as &$field ) {
			if ( 2 == $field->id ) {
				$field->defaultValue = date( 'Y-m-d', $yesterday );
			}
		}
	} elseif ( ( 20 <= $current_hour ) && ( $current_hour <= 23 ) ) {
		$form['fields'][2]['defaultValue'] = date( 'Y-m-d', $current_timestamp );
		$form['description'] = sprintf(
			'Enter points for today (%s)',
			date( 'l n/j', $current_timestamp )
		);
		foreach ( $form['fields'] as &$field ) {
			if ( 2 == $field->id ) {
				$field->defaultValue = date( 'Y-m-d', $current_timestamp );
			}
		}
	} else {
		$form['scheduleForm'] = true;
		$form['scheduleStart'] = date( 'm/d/Y', $current_timestamp );
		$form['scheduleStartHour'] = 20;
		$form['scheduleStartMinute'] = 0;
		$form['scheduleStartAmpm'] = 'pm';
		$form['scheduleMessage'] = 'You may enter points for today starting at 8pm';
	}
	return $form;
}