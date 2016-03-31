<?php 
/***************************************************************************
Plugin Name:  AUS Telegram Bot Notifier
Plugin URI:   http://wp.ulugov.uz
Description:  Sends Wordpress Posts to Telegram channel via Telegram Bot
Version:      1.0.3
Author:       Anvar Ulugov
Author URI:   http://anvar.ulugov.uz
License:      GPLv2 or later
**************************************************************************/

defined( 'ABSPATH' ) or die( "No script kiddies please!" );
/*
 * Define plugin absolute path and url
 */
define( 'AUSTB_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'AUSTB_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );
include( AUSTB_DIR . '/class-options.php' );

class AUS_Telegram_Bot {

	private $options;
	private $last_send;

	function __construct() {

		$options_configs = array(
			'options' => 'aus-telegram-bot_plugin_options',
			'plugin_name' => 'AUS Telegram Bot',
			'plugin_slug' => 'aus-telegram-bot',
		);
		$AUS_tb_options = new AUS_tb_options( $options_configs );
		// Initialize
		$this->init();
		// Add action to schedule telegram_send function
		add_action( 'aus_telegram_bot_schedule', array( $this, 'telegram_send') );
		$this->telegram_send_scheduler();

		// Check if changed the recurrence of scheduled event. If yes unschedule the current and schedule new one.
		$recurrence = wp_get_schedule( 'aus_telegram_bot_schedule' );

		if ( $recurrence != $this->options['recurrence'] ) {
			wp_clear_scheduled_hook( 'aus_telegram_bot_schedule' );
			wp_schedule_event( time(), $this->options['recurrence'], 'aus_telegram_bot_schedule' );
		}

	}

	/*
	 * Class init function
	 */
	public function init() {
		$this->options 	= get_option( 'aus-telegram-bot_plugin_options' );
		$this->last_send = get_option( 'aus_telegram_bot_last_send' );
	}

	public function telegram_send() {

		$post = $this->get_post();
		if ( $post ) {
			$ch = curl_init(); 
			curl_setopt( $ch, CURLOPT_URL, "https://api.telegram.org/bot" . $this->options['bot_token'] . "/sendMessage?chat_id=" . $this->options['channelname'] . "&parse_mode=Markdown&disable_web_page_preview=false&text=" . urlencode( $this->options['before_text'] . "\n" . "[" . $post['title'] . "](" . $post['url'] . ")\n*" . $post['category'] . "* | _" . $post['date'] . "_\n" . $post['text'] . "\n" . $this->options['after_text'] ) ); 
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); 
			$output = curl_exec( $ch ); 
			curl_close( $ch ); 
			update_post_meta( $post['id'], 'aus_telegram_sent', 1 );
			update_option( 'aus_telegram_bot_last_send', date( 'Y-m-d H:i:s' ) );
		}
		
	}

	private function get_post() {
		if ( isset( $this->options['text_limit'] ) && ! empty( $this->options['text_limit'] ) ) {
			$limit = $this->options['text_limit'];
		} else {
			$limit = 100;
		}
		$start_date = date( 'F jS, Y', strtotime( $this->options['start_date'] ) );
		$getPost = new WP_Query( array(
			'cat' => implode( ',', $this->options['categories'] ),
			'date_query' => array(
				array(
					'after'	=> $start_date,
				),
			),
			'orderby' => 'date',
			'order' => 'ASC',
			'meta_query' => array(
			   'relation' => 'OR',
				array(
					'key' => 'aus_telegram_sent',
					'compare' => 'NOT EXISTS', // works!
					'value' => '' // This is ignored, but is necessary...
				)
			),
			'posts_per_page' => 1
		) );
		if ( isset( $getPost->posts[0] ) ) {
			$getPost = $getPost->posts[0];
			$text = $getPost->post_content;
			$text = strip_shortcodes($text);
			$text = preg_replace('/[\r\n]+/', "", $text);
			$text = preg_replace('/\s+/', ' ', $text);
			$text = trim( $text );
			$text = strip_tags($text);
			$text = $this->limit( $text, $limit );
			$cat_ids = wp_get_post_categories( $getPost->ID );
			if ( ! empty( $cat_ids ) ) {
				$category = get_category( $cat_ids[0] )->name;
			} else {
				$category = '';
			}
			$post = array(
				'id' => $getPost->ID,
				'title' => $getPost->post_title,
				'url' => $getPost->guid,
				'date' => date( 'd.m.Y', strtotime( $getPost->post_date ) ),
				'category' => $category,
				'text' => $text,
			);
			wp_reset_query();
			return $post;
		} else {
			return false;
		}
		
	}

	private function limit( $text, $limit = 100 ) {

		$cyr_chars = 'АаБбВвГгДдЕеЁёЖжЗзИиЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯя';

		if ( str_word_count( $text, 0, $cyr_chars ) >= $limit ) {
			$words = str_word_count( $text, 2, $cyr_chars );
			$pos = array_keys( $words );
			$text = substr( $text, 0, $pos[ $limit ] );
		}

		return $text;

	}

	public function telegram_send_scheduler() {

		if ( ! wp_next_scheduled( 'aus_telegram_bot_schedule' ) ) {

			if ( ! isset( $this->options['recurrence'] ) or $this->options['recurrence'] == '' ) {
				$this->options['recurrence'] = 'hourly';
			}

			wp_schedule_event( time(), $this->options['recurrence'], 'aus_telegram_bot_schedule' );

		}

	}
}
$AUS_Telegram_Bot = new AUS_Telegram_Bot();