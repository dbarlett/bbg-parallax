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

/**
 * Limit entries for form #3 (Total Wellness Challenge Points Log1)
 * @link http://www.gravityhelp.com/documentation/gravity-forms/extending-gravity-forms/hooks/filters/gform_pre_render/
 * @author Dylan Barlett <dylan.barlett@gmail.com>
 */
add_filter( 'gform_pre_render_3', 'bbg_gform_pre_render_3', 10, 1 );
function bbg_gform_pre_render_3( $form ) {
	// Local time based on blog timezone
	$current_timestamp = current_time( 'timestamp' );
	$current_hour = current_time( 'H' );

	if ( ( 15 <= $current_hour ) && ( $current_hour <= 19 ) ) {
		// Between 3pm and 8pm, close form as if it were scheduled
		$form['scheduleForm']           = true;
		$form['scheduleStart']          = date( 'm/d/Y', $current_timestamp );
		$form['scheduleStartHour']      = 8;
		$form['scheduleStartMinute']    = 0;
		$form['scheduleStartAmpm']      = 'pm';
		$form['schedulePendingMessage'] = 'You may enter points for today starting at 8pm';
	} else {
		// At all other times, form is open
		if ( ( 0 <= $current_hour ) && ( $current_hour <= 14 ) ) {
			// Between midnight and 3pm, enter points for previous day
			// Subtract 16 hours to account for DST transitions
			$entry_for = $current_timestamp - ( 60 * 60 * 16 );
			$description = 'Enter points for yesterday (%s)';
		} else {
			// Between 8pm and midnight, enter points for current day
			$entry_for = $current_timestamp;
			$description = 'Enter points for today (%s)';
		}

		// Find existing points for the current time period
		$duplicate_entry = false;
		$entry_for_ymd = date( 'Y-m-d', $entry_for );
		$acf_user_id = 'user_' . get_current_user_id();
		if ( has_sub_field( 'points', $acf_user_id ) ) {
			while ( has_sub_field( 'points', $acf_user_id ) ) {
				if ( get_sub_field( 'date' ) == $entry_for_ymd ) {
					$duplicate_entry = true;
					break;
				}
			}
		}

		if ( $duplicate_entry ) {
			// Close form as if the total entry limit had been reached
			$form['limitEntries']        = true;
			$form['limitEntriesCount']   = 0;
			$form['limitEntriesMessage'] = sprintf(
				'You have already entered points for %s.',
				date( 'l n/j', $entry_for )
			);
		} else {
			$form['description'] = sprintf(
				$description,
				date( 'l n/j', $entry_for )
			);
			foreach ( $form['fields'] as &$field ) {
				if ( 2 == $field->id ) {
					$field->defaultValue = date( 'Y-m-d', $entry_for );
				}
			}
		}
	}
	return $form;
}

/**
 * Validate field 2 on form #3 (points date)
 * @link http://www.gravityhelp.com/documentation/gravity-forms/extending-gravity-forms/hooks/filters/gform_field_validation/
 * @author Dylan Barlett <dylan.barlett@gmail.com>
 */
add_filter( 'gform_field_validation_3_2', 'bbg_validate_points_date_3_2', 10, 4 );
function bbg_validate_points_date_3_2( $result, $value, $form, $field ) {
	if ( $result['is_valid'] ) {
		// Find existing points for the current time period
		$duplicate_entry = false;
		$entry_for_ymd = date( 'Y-m-d', $entry_for );
		$acf_user_id = 'user_' . get_current_user_id();
		if ( has_sub_field( 'points', $acf_user_id ) ) {
			while ( has_sub_field( 'points', $acf_user_id ) ) {
				if ( get_sub_field( 'date' ) == $entry_for_ymd ) {
					$duplicate_entry = true;
					break;
				}
			}
		}

		if ( $duplicate_entry ) {
			$result['is_valid'] = false;
			$result['message']  = 'You have already entered points for this day.';
		}
	}
	return $result;
}

/**
 * Award bonus points for Total Wellness Challenge (form #3)
 * @link http://www.gravityhelp.com/documentation/gravity-forms/extending-gravity-forms/hooks/actions/gform_pre_submission/
 * @author Dylan Barlett <dylan.barlett@gmail.com>
 */
add_filter( 'gform_pre_submission_3', 'bbg_gform_pre_submission_3', 10, 1 );
function bbg_gform_pre_submission_3( $form ) {
	// Field 6 (Nutrition Points)
	if ( 12 == rgpost( 'input_6' ) ) {
		$points_date = rgpost( 'input_2' ); // Y-m-d
		$last_four_days = array();
		for ( $i = 1; $i <= 4; $i++ ) {
			$past_date = new DateTime( $points_date );
			$last_four_days[] = $past_date->sub( new DateInterval( 'P' . $i . 'D' ) )->format( 'Y-m-d' );
		}
		$search_criteria = array(
			'status'		=> 'active',
			'field_filters' => array(
				'mode' => 'all',
				array(
					'key'   => 'created_by',
					'value'	=> get_current_user_id(),
				),
				array(
					'key'      => '2', // points date (Y-m-d)
					'operator' => 'in',
					'value'	   => $last_four_days,
				),
				array(
					'key'      => '6', // nutrition points
					'operator' => 'is',
					'value'	   => '12',
				),
			),
		);
		$sorting = array(
			'key'       => '2', // points date (Y-m-d)
			'direction' => 'DESC',
		);
		$paging = array(
			'offset'    => 0,
			'page_size' => 4,
		);
		$entries = GFAPI::get_entries( 3, $search_criteria, $sorting, $paging );
		if ( ! is_wp_error( $entries ) && ( 4 == count( $entries ) ) ) {
			$_POST['input_6'] = '13';
		}
	}
	return $form;
}

add_action( 'gform_after_submission_3', 'acf_post_submission', 10, 2 );
function acf_post_submission( $entry, $form ) {
	$field_key = 'field_54817fefd7196';
	$user_id = 'user_' . get_current_user_id();
	$value = array();
	$value[] = get_field( $field_key, $user_id );
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