<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<div class="th_wrapper">
		<div id="th_main">
			<div class="th_content">

				<form method="post" action="options.php">
					<?php 
					settings_fields( $this->settings_fields_slug );
					do_settings_sections( $this->settings_section_slug );
					submit_button();
					?>
				</form>

			</div><!-- .th_content -->
		</div><!-- #th_main -->
	</div><!-- .th_wrapper -->
</div><!-- .wrap -->
