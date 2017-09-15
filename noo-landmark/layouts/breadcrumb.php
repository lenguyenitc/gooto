<div class="noo-page-breadcrumb">

<?php
	if( function_exists('bcn_display') ) :

		$sepa_default = '<i class="icon ion-ios-arrow-forward"></i>';
		$bcn_display = bcn_display(true);

		// Replace default by theme
		$searchReplaceArray = array(
			' &gt; ' => $sepa_default,
			' &gt;'  => $sepa_default,
			'&gt; '  => $sepa_default,
			'&gt;'   => $sepa_default
		);

		$bcn_display = str_replace(
			array_keys($searchReplaceArray), 
			array_values($searchReplaceArray), 
			$bcn_display
		);

		echo noo_landmark_func_html_content_filter($bcn_display); 

	else :
		if ( class_exists( 'woocommerce' ) ) {
			woocommerce_breadcrumb();
		} else {
			noo_landmark_func_the_breadcrumb();
		}
	endif;
	
?>

</div>