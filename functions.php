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
 * Return current user's nutrition points for a given date
 * @link http://www.advancedcustomfields.com/resources/has_sub_field/
 * @author Dylan Barlett <dylan.barlett@gmail.com>
 * @param string $date_ymd Y-m-d
 * @return int points, or -1 if no entry for given date
 */
function bbg_current_user_nutrition_points( $date_ymd ) {
	$acf_user_id = 'user_' . get_current_user_id();
	$points = -1;
	if ( get_field( 'points', $acf_user_id ) ) {
		while ( has_sub_field( 'points', $acf_user_id ) ) {
			if ( get_sub_field( 'date' ) == $date_ymd ) {
				$points = get_sub_field( 'total_nutrition_points_earned' );
				// Don't break out of loop or return here (ACF keeps loop state)
			}
		}
	}
	return $points;
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
		if ( -1 != bbg_current_user_nutrition_points( date( 'Y-m-d', $entry_for ) ) ) {
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
//add_filter( 'gform_field_validation_3_2', 'bbg_validate_points_date_3_2', 10, 4 );
function bbg_validate_points_date_3_2( $result, $value, $form, $field ) {
	if ( $result['is_valid'] ) {
		// Find existing points for the current time period
		if ( -1 != bbg_current_user_nutrition_points( $value ) ) {
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
		$last_four_days_points = 0;
		for ( $i = 1; $i <= 4; $i++ ) {
			$past_date = new DateTime( $points_date );
			$past_date->sub( new DateInterval( 'P' . $i . 'D' ) );
			$last_four_days_points += bbg_current_user_nutrition_points( $past_date->format( 'Y-m-d' ) );
		}
		if ( 48 == $last_four_days_points ) { // 4 days of exactly 12 points
			$_POST['input_6'] = '13';
		}
	}
	return $form;
}

/**
 * Store points in ACF repeater
 * @link http://www.advancedcustomfields.com/resources/update_field/
 * @author Unknown
 * @author Dylan Barlett <dylan.barlett@gmail.com>
 */
add_action( 'gform_after_submission_3', 'acf_post_submission', 10, 2 );
function acf_post_submission( $entry, $form ) {
	$field_key = 'field_54817fefd7196';
	$acf_user_id = 'user_' . get_current_user_id();
	$value = get_field( $field_key, $acf_user_id, false );
	$value[] = array(
		'date'                          => str_replace( '-', '', $entry[2] ),
		'total_nutrition_points_earned' => $entry[6],
		'total_fitness_points_earned'   => $entry[7],
		'total_wellness_points_earned'  => $entry[8],
	);
	// print_r($value); die;
	error_log( print_r( $value, 1 ), 3, '/tmp/my-errors.log' );
	update_field( $field_key, $value, $acf_user_id );
}