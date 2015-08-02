<?php
/*
 * Plugin Name: BBPress Series Topics
 * Plugin URI:  https://github.com/wikitopian/bbp-series-topics
 * Description: Enable authors to bundle their topics into series.
 * Text Domain: bbp-series-topics
 * Version:     0.1
 * Author:      @wikitopian
 * Author URI:  http://www.github.com/wikitopian
 * License:     LGPLv3
 */

class Bbp_Series_Topics {

	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( &$this, 'do_style' ) );

	}

	public function do_style() {

		wp_enqueue_style(
			'bbp-series-topics',
			plugins_url( '/style/bbp-series-topics.css', __FILE__ )
		);

	}

}

$bbp_series_topics = new Bbp_Series_Topics();

/* EOF */
