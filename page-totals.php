<?php
#error_reporting(E_ALL);
ini_set( 'display_errors', 1 );
define( 'DAY', 86400 );

// start and end dates
$start = date( 'Y-m-09' );
$end = date( 'Y-m-' . date( 't' ) );


// ### Redeclare the above 2 variables if you wish to apply a different range:
// $start = '2015-05-04';
// $end = '2015-05-30';

# Suggested dynamic solution, it will assume the start date and add 45 days.
# So in february it will cover up to feb 28
$end = date( 'Y-m-d', strtotime( $start ) + 27 * DAY );

// count how many days we'll show
$days = ( strtotime( $end ) - strtotime( $start ) ) / DAY;
// count how many months we have
$months = count_months( $start, $end );


// echo "[debug] $months months $days days";


/**
 * BodyByGinny Totals Template.
 *
 * @package      BodyByGinny
 * @since        1.0.0
 * @copyright    Copyright (c) 2014, ChillyBin WordPress Web Design
 * @license      GPL-2.0+
*/

?>

<?php get_header(); ?>

<style>
	.sidebar-none #content {
		overflow-x: scroll;
	}
	table.table {
			border-collapse: collapse;
			border-spacing: 0;
			width: 100%;
			margin-bottom: 20px;
			border: none;
			font-family: Arial, sans-serif;
	}
	table.table td,
	table.table th {
			padding: 8px !important;
			border: none;
			background: none;
			text-align: center;
			float: none !important
	}
	table.table tbody tr:first-of-type td {
		border-top: 1px solid #ddd;
	}
	table.table thead > tr > td:first-of-type {
			border-top: 0;
			text-align: left !important;
			width: 200px !important;
			display: block;
	}
	table.table > tbody > tr > td:first-of-type {
			text-align: left !important;
	}
	table.table thead th {
			border-bottom: 1px solid #ddd;
	}
	table.table thead th {
			background-color: #ad71b1;
			font-weight: 700;
			vertical-align: middle
	}
	table.table tbody td {
			vertical-align: top
	}
	table.table tbody tr:nth-child(odd) td {
			background-color: #f9f9f9;
	}
	table.table tbody tr:nth-child(even) td {
			background-color: #fff;
	}
	table.table tbody tr:hover td {
			background-color: #f3f3f3;
	}
	table.table tr td table tr td.score {
		background: #999;
		color: #fff;
	}
	table.table tr td table tr td.point-type {
		background: #333;
		color: #fff;
	}
	table.table td.type-n {
		background: #ad71b1!important;
	}
	table.table td.type-f {
		background: #07b9f5!important;
	}
	table.table td.type-w {
		background: #bed732!important;
	}
	table.table tr td table tr td.type-t {
		background: #fdbf45;
	}
</style>

<?php
/** Themify Default Variables
 *  @var object */
global $themify;

if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

<!-- layout-container -->
<div id="layout" class="pagewidth clearfix">

	<?php themify_content_before(); // hook ?>

	<!-- content -->
	<div id="content" class="list-post">
			<?php themify_content_start(); // hook ?>

			<?php

			$args = array(
				'role' => 'pmpro_role_7',
				'orderby' => 'meta_value',
				'meta_key' => 'last_name',
			);
			// The Query
			$user_query = new WP_User_Query( $args );
			$nutrition = 0;
			$fitness = 0;
			$wellness = 0;
			// User Loop
			?>
			<table class="table">
				<thead>
				<tr>
					<td><strong>Name</strong></td>
					<td><strong>Totals</strong></td>
					<?php for ( $i = 0; $i <= $months - 1 ; $i++ ): ?>
						<td colspan="<?php echo date( 't', $d = strtotime( $start ) + $i * 31 * DAY ); ?>"><strong><?php echo date( 'F', $d ); ?></strong></td>
					<?php endfor; ?>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td><strong></strong></td>
					<td>
						<strong>&nbsp;</strong>
						<table>
							<tr class="point-container">
								<td class="point-type type-n">N</td>
								<td class="point-type type-f">F</td>
								<td class="point-type type-w">W</td>
								<td class="point-type type-t">T</td>
							</tr>
						</table>
					</td>
					<?php
					// $days = dates_month(date('m'), date('Y'));
					// foreach ($days as $date):
					$init = strtotime( $start ) - DAY;
					for ( $date = 1; $date <= $days + 1; $date++ ):
						$day = date( 'd', $init += DAY );
						?>
						<td class="date">
							<strong><?php echo $day; ?></strong>
							<table>
								<tr class="point-container">
									<td class="point-type type-n">N</td>
									<td class="point-type type-f">F</td>
									<td class="point-type type-w">W</td>
								</tr>
							</table>
						</td>
					<?php endfor; ?>
				</tr>
			<?php

			if ( ! empty( $user_query->results ) ) {
				global $current_user;
				foreach ( $user_query->results as $user ) { ?>
					<tr>
						<td class="fixed"><?
						$current_user = get_user_meta($user->ID);
						$name = $current_user['first_name'][0] . ' ' . $current_user['last_name'][0];

						// if ($name != 'Shaan Nicol') continue; #DEBUG

						echo $name ? $name : $user->display_name;
						?></td>
						<?php
						// initiate the variables
						$n = $f = $w = $t = $t_n = $t_f = $t_w = $t_t = 0;
						$points_cells = [];
						while ( has_sub_field( 'points', 'user_' . $user->ID ) ) {
							// $points_date = explode('/', get_sub_field('date'));
							$points_date = get_sub_field( 'date' );
							$t_n += $n = get_sub_field( 'total_nutrition_points_earned' );
							$t_f += $f = get_sub_field( 'total_fitness_points_earned' );
							$t_w += $w = get_sub_field( 'total_wellness_points_earned' );
							// $t_t += get_sub_field('total_nutrition_points_earned') + get_sub_field('total_fitness_points_earned') + get_sub_field('total_wellness_points_earned');
							$t = $n + $f + $w;
							$t_t += $t;
							// echo "$name: $n $f $w $t, $t_n $t_f $t_w $t_t\n";

							$points_cells[ $points_date ] = [
								'nutrition' => $n,
								'fitness' => $f,
								'wellness'  => $w,
								// 'date' => $points_date
							];
						} ?>
							<td class="date">
							<table>
								<tr class="point-container">
									<td class="point-type type-n"><?php echo (int) $t_n; ?></td>
									<td class="point-type type-f"><?php echo (int) $t_f; ?></td>
									<td class="point-type type-w"><?php echo (int) $t_w; ?></td>
									<td class="point-type type-t"><?php echo (int) $t_t; ?></td>
								</tr>
							</table>
						</td>
						<?php #foreach($days as $date):
						$init = strtotime( $start ) - DAY;
						for ( $date = 1; $date <= $days + 1; $date++ ):
							$fdate = date( 'Y-m-d', $init += DAY );
							if ( isset( $points_cells[ $fdate ] ) ) {
								$nutrition = $points_cells[ $fdate ]['nutrition'];
								$fitness   = $points_cells[ $fdate ]['fitness'];
								$wellness  = $points_cells[ $fdate ]['wellness'];
							} else {
								$nutrition = $fitness = $wellness = '0';
							}
						?>
								<td>
									<table>
										<tr>
											<td class="score type-n"><?php echo $nutrition; ?></td>
											<td class="score type-f"><?php echo $fitness;   ?></td>
											<td class="score type-w"><?php echo $wellness;  ?></td>
										</tr>
									</table>
								</td>
				<?php
					endfor;
				}
			} else {
				echo 'No users found.';
			}
			?>
					</tr>
				</tbody>
			</table><?php
			?>

		<?php themify_content_end(); // hook ?>
	</div>
	<!-- /content -->

		<?php themify_content_after(); // hook ?>

<?php endwhile; ?>

<?php
/////////////////////////////////////////////
// Sidebar
/////////////////////////////////////////////
if ( 'sidebar-none' != $themify->layout ) {
	get_sidebar();
}
?>

</div>
<!-- /layout-container -->

<?php
get_footer();

function count_months( $date1, $date2 ) {
	$ts1 = strtotime( $date1 );
	$ts2 = strtotime( $date2 );

	$year1 = date( 'Y', $ts1 );
	$year2 = date( 'Y', $ts2 );

	$month1 = date( 'm', $ts1 );
	$month2 = date( 'm', $ts2 );

	return 1 + (( $year2 - $year1 ) * 12 ) + ( $month2 - $month1 );
}

function dates_month( $month, $year ) {
	$num = cal_days_in_month( CAL_GREGORIAN, $month, $year );
	$dates_month = array();
	for ( $i = 1; $i <= $num; $i++ ) {
		$mktime = mktime( 0, 0, 0, $month, $i, $year );
		$date = date( 'd', $mktime );
		$dates_month[ $i ] = $date;
	}
	return $dates_month;
}
?>