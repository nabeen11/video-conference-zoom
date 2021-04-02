<?php


namespace Codemanas\VczApi\Blocks;


class Blocks {
	public static $_instance = null;

	public static function get_instance() {
		return is_null( self::$_instance ) ? self::$_instance = new self() : self::$_instance;
	}

	public function __construct() {

		add_filter( 'block_categories', [ $this, 'register_block_categories' ], 10, 2 );
		add_action( 'init', [ $this, 'register_scripts' ] );
		add_action( 'init', [ $this, 'register_blocks' ] );
		//add_action( 'wp_ajax_vczapi_get_categories_for_gb', [ $this, 'get_categories' ] );
	}

	public function register_scripts() {
		$script_asset_path = require_once( ZVC_PLUGIN_DIR_PATH . '/build/index.asset.php' );

		//Plugin Scripts
		wp_register_style( 'video-conferencing-with-zoom-api-blocks',
			ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/css/style.css',
			false,
			ZVC_PLUGIN_VERSION );
		//print(ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/css/style.css'); die;

		wp_register_style(
			'vczapi-blocks-style',
			plugins_url( '/build/index.css', ZVC_PLUGIN_FILE ),
			[ 'video-conferencing-with-zoom-api-blocks' ],
			$script_asset_path['version']
		);


		wp_register_script(
			'vczapi-blocks',
			plugins_url( '/build/index.js', ZVC_PLUGIN_FILE ),
			$script_asset_path['dependencies'],
			$script_asset_path['version']
		);
	}

	public function register_block_categories( $categories, $post ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'vczapi-blocks',
					'title' => __( 'Zoom', 'video-conferencing-with-zoom-api' ),
					//'icon'  => 'wordpress',
				),
			)
		);
	}

	public function register_blocks() {

		register_block_type( 'vczapi/list-meetings', [
			"title"           => "List Zoom Meetings",
			"attributes"      => [
				"shortcodeType"    => [
					"type"    => "string",
					"default" => "meeting"
				],
				"showPastMeeting"  => [
					"type"    => "boolean",
					"default" => false
				],
				"showFilter"       => [
					"type"    => "string",
					"default" => "yes",
				],
				"postsToShow"      => [
					"type"    => "number",
					"default" => 5
				],
				"orderBy"          => [
					"type"    => "string",
					"default" => ""
				],
				"selectedCategory" => [
					"type"    => "array",
					"default" => []
				],
				"selectedAuthor"   => [
					"type"    => "number",
					"default" => 0
				],
				"displayType"      => [
					"type"    => "string",
					"default" => ""
				],
				"columns"          => [
					"type"    => "number",
					"default" => 3
				]
			],
			"category"        => "vczapi-blocks",
			"icon"            => "list-view ",
			"description"     => "List Upcoming or Past Meetings/Webinars",
			"textdomain"      => "video-conferencing-with-zoom-api",
			'editor_script'   => 'vczapi-blocks',
			'editor_style'    => 'vczapi-blocks-style',
			'render_callback' => [ $this, 'render_list_meetings' ]
		] );

		register_block_type( 'vczapi/show-meeting-post', [
			"title"           => "Embed Zoom Meeting",
			"attributes"      => [
				"postID" => [
					"type"    => "number",
					"default" => 0
				]
			],
			"category"        => "vczapi-blocks",
			"icon"            => "sticky",
			"description"     => "Show a Meeting Post with Countdown",
			"textdomain"      => "video-conferencing-with-zoom-api",
			'editor_script'   => 'vczapi-blocks',
			'editor_style'    => 'vczapi-blocks-style',
			'render_callback' => [ $this, 'render_meeting_post' ]
		] );
	}

	public function render_list_meetings( $attributes ) {


		$shortcode = isset( $attributes['shortcodeType'] ) && ( $attributes['shortcodeType'] == 'webinar' ) ? 'zoom_list_webinars' : 'zoom_list_meetings';


		if ( isset( $attributes['postsToShow'] ) && ! empty( $attributes['postsToShow'] ) ) {
			$shortcode .= ' per_page="' . $attributes['postsToShow'] . '"';
		}

		if ( isset( $attributes['orderBy'] ) && ! empty( $attributes['orderBy'] ) ) {
			$shortcode .= ' order="' . $attributes['orderBy'] . '"';
		}

		if ( isset( $attributes['showFilter'] ) && ! empty( $attributes['showFilter'] ) ) {
			$shortcode .= ' filter="' . $attributes['showFilter'] . '"';
		}

		if ( isset( $attributes['selectedCategory'] ) && is_array( $attributes['selectedCategory'] ) && ! empty( $attributes['selectedCategory'] ) ) {
			$categories_string = '';
			$category_count    = count( $attributes['selectedCategory'] );
			$separator         = ( $category_count > 1 ) ? ',' : '';
			foreach ( $attributes['selectedCategory'] as $index => $category ) {
				if ( $category['value'] == '' ) {
					continue;
				}
				$separator         = ( $index + 1 ) ? $separator : '';
				$categories_string .= $category['value'] . $separator;
			}
			unset( $separator );

			if ( ! empty( $categories_string ) ) {
				$shortcode .= ' category="' . $categories_string . '"';
			}
		}

		if ( isset( $attributes['selectedAuthor'] ) && ! empty( $attributes['selectedAuthor'] ) ) {
			$shortcode .= ' author="' . $attributes['selectedAuthor'] . '"';
		}

		if ( isset( $attributes['displayType'] ) && ! empty( $attributes['displayType'] ) ) {
			$shortcode .= ' type="' . $attributes['displayType'] . '"';
		}

		if ( isset( $attributes['columns'] ) && ! empty( $attributes['columns'] ) ) {
			$shortcode .= ' cols="' . $attributes['columns'] . '"';
		}

		return do_shortcode( '[' . $shortcode . ']' );
	}

	public function render_meeting_post( $attributes ) {
		$shortcode = 'zoom_meeting_post';
		if ( isset( $attributes['postID'] ) && ! empty( $attributes['postID'] ) ) {
			$shortcode .= ' post_id="' . $attributes['postID'] . '"';
		}
		
		ob_start();
		echo do_shortcode( '['.$shortcode.']');
		return ob_get_clean();
	}
}

Blocks::get_instance();