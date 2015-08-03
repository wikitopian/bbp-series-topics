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

	private $settings;

	public function __construct() {

		$this->settings = get_option(
			'bbp-series-topics',
			array(

				'posts_per_page' => 999,
				'key' => '_bbp-series-topic_parent',
				'post_type' => 'topic',
				'post_parent' => null,

			)
		);

		update_option( 'bbp-series-topics', $this->settings );

		add_action( 'wp_enqueue_scripts', array( &$this, 'do_style' ) );

		add_action( 'bbp_template_before_single_topic', array( &$this, 'do_display' ) );

		add_action( 'bbp_theme_after_topic_form_content', array( &$this, 'do_form' ) );

		add_action( 'save_post', array( &$this, 'do_form_save' ), 10, 2 );

	}

	public function do_style() {

		wp_enqueue_style(
			$this->settings['key'],
			plugins_url( '/style/bbp-series-topics.css', __FILE__ )
		);

	}

	public function do_display() {

		global $post;

		$post_series = get_post_meta( $post->ID, $this->settings['key'], true );

		if( $post_series == 0 ) {
			$parent = true;
			$post_series = $post->ID;
		} else {
			$parent = false;
		}

		$series = $this->get_series(
			$post->post_author,
			$post_series
		);

		if( $parent && empty( $series ) ) {
			return; // Not part of a series
		}

		$series_parent = get_post( $post_series );


		$series_title_url = get_permalink( $series_parent->ID );

		$series_items_raw = "<li><a href=\"%s\">%s</a></li>\n";
		$series_items_raw_bare = "<li>%s</li>\n";


		$series_items = '';
		foreach( $series as $article ) {


			$series_items_link = get_permalink( $article->ID );

			$series_items_title = $article->post_title;

			if( $post->ID != $article->ID ) {
				$series_items .= sprintf(
					$series_items_raw,
					$series_items_link,
					$series_items_title
				);
			} else {
				$series_items .= sprintf(
					$series_items_raw_bare,
					$series_items_title
				);
			}

		}

		if( $post->ID != $series_parent->ID ) {
			$series_header = "Series: <a href=\"{$series_title_url}\">{$series_parent->post_title}</a>";
		} else {
			$series_header = "Series: " . $series_parent->post_title;
		}

		echo <<<SERIES

<ul class="forums bbp-replies {$this->settings['key']}">

	<li class="bbp-header">

		{$series_header}

	</li>

	<li class="bbp-body">

		<ul>

			{$series_items}

		</ul>

	</li>

</ul>

SERIES;

	}

	public function do_form() {

		global $post;

		if( !empty(  $this->settings['post_parent'] ) ) {

			if( $this->settings['post_parent'] != $post->post_parent ) {

				if( $this->settings['post_parent'] != $post->ID ) {
					return;
				}

			}

		}

		$user_id = bbp_get_displayed_user_id();

		if( empty( $user_id ) || $user_id == 0 ) {
			$user_id = get_current_user_id();
		}

		$series_posts = $this->get_series( $user_id );

		$series_selected = get_post_meta( $post->ID, $this->settings['key'], true );

		$series_raw = "<option value=\"%s\" class=\"level-0\" %s>%s</option>\n";

		$series = '';
		foreach( $series_posts as &$series_post ) {

			if( $series_post->ID == $post->ID ) {
				continue;
			}

			$series_post->series = $series_post->ID;

			if( !empty( $series_selected ) && $series_selected == $series_post->series ) {
				$selected = true;
			} else {
				$selected = false;
			}

			if( $selected ) {
				$selected = 'selected="selected"';
			} else {
				$selected = '';
			}

			$series .= sprintf( $series_raw, $series_post->series, $selected, $series_post->post_title );

		}

		echo <<<SERIES

<p>
	<label for="{$this->settings['key']}">Add to Series:</label><br />
		<select name="{$this->settings['key']}" id="{$this->settings['key']}" tabindex="105">

			<option value="0" class="level-0">(No Series)</option>

			{$series}

	</select>
</p>

SERIES;

	}

	public function do_form_save( $post_id, $forum_post ) {

		if( isset( $_REQUEST[$this->settings['key']] ) ) {
			$series = $_REQUEST[$this->settings['key']];

			update_post_meta( $post_id, $this->settings['key'], $series );

		}

	}

	public function get_series( $post_author, $parent_id = 0 ) {

		$get_series_args = array(

			'posts_per_page' => $this->settings['posts_per_page'],
			'post_type' => $this->settings['post_type'],
			'post_parent' => $this->settings['post_parent'],
			'author' => $post_author,
			'meta_query' => array(
				array(
					'key' => $this->settings['key'],
					'value' => $parent_id,
				),
			),

		);

		if( empty( $this->settings['post_parent'] ) ) {
			unset( $get_series_args['post_parent'] );
		}

		return get_posts( $get_series_args );

	}

}

$bbp_series_topics = new Bbp_Series_Topics();

/* EOF */
