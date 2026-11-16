<?php
/**
 * CLI import runner.
 *
 * @package VL_Core
 */

@set_time_limit( 0 );
$with_images = true;
if ( isset( $args ) && in_array( '--no-images', $args, true ) ) {
	$with_images = false;
}

$stats = vl_import_scraped_catalog( $with_images );
echo wp_json_encode( $stats, JSON_PRETTY_PRINT ) . PHP_EOL;
