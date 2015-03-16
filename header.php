<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<?php
/** Themify Default Variables
 *  @var object */
	global $themify; ?>
<meta charset="<?php bloginfo( 'charset' ); ?>">

<title><?php if (is_home() || is_front_page()) { bloginfo('name'); } else { echo wp_title(''); } ?></title>

<?php
/**
 *  Stylesheets and Javascript files are enqueued in theme-functions.php
 */
?>

<!-- wp_header -->
<?php wp_head(); ?>
<meta name="google-site-verification" content="QPJUbasU57NqjKDLLYd37BV6Rrcw2QEruXZ2KBT5400" />
</head>

<body <?php body_class(); ?>>
<?php themify_body_start(); // hook ?>
<div id="pagewrap">
<div id="headerwrap" <?php $themify->theme->custom_header_background(); ?> >

<div id="nav-bar" class="clearfix"><div class="bbg"><a href="http://www.bodybyginny.com"><img src="http://www.bodybyginny.com/wp-content/uploads/2014/09/BbGlogo2014-web-111x109.png"></a></div>
				<p class="titletop">Outdoor Fitness Classes in Arlington and McLean</p>
<div id="menu-icon" class="mobile-button"><span><?php _e('Menu', 'themify'); ?></span></div>
<nav>
<!--<div class="toplogin"><a href="http://www.bodybyginny.com/fall-total-wellness-challenge/log-daily-challenge-points/"><img src="http://www.bodybyginny.com/wp-content/uploads/2014/09/twc-logo-trans70.png" width="70" height="70" style="margin-right: 10px" alt="Total Wellness Challenge"><span style="vertical-align: top">LOG TWC POINTS</span></a></div>-->
					
					<?php themify_theme_menu_nav(); ?>
					<!-- /#main-nav --> 
				</nav>
			
</div>

 <?php if ( is_front_page() ):?>	

<?php else : ?>

<?php endif; ?>
<?php themify_header_before(); // hook ?>


		<header id="header">
 <?php if ( is_front_page() ):?>

<?php putRevSlider("home") ?>
<?php else : ?>
<?php putRevSlider("subpages") ?>
<?php endif; ?>

	  
			<?php themify_header_start(); // hook ?>
			
			
			<?php themify_header_end(); // hook ?>
		</header>
        
       <!-- <?php
 if(pmpro_hasMembershipLevel('3'))
 {
 ?>
 <style>
 .module-box-content.ui.default, .module.module-widget.widget-2386-0-1-0 {
    display: block;
}

</style>
 <?php
 } else { ?> 
 
  <style>
 .module-box-content.ui.default, .module.module-widget.widget-2386-0-1-0 {
    display: none;
}

</style>
 
 <?php } ?>-->



		<!-- /#header -->
        <?php themify_header_after(); // hook ?>
				

</div><!-- /#headerwrap -->
	
	
<?php putRevSlider("announce") ?>
	<div id="body" class="clearfix">
    <?php themify_layout_before(); //hook ?>
