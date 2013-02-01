<?php
/**
 * @package FM_Portal
 * @version 1.0.1
 */
/*
Plugin Name: FM Portal
Plugin URI: http://footballmanagerportal.co.uk
Description: This is a plugin for the Football Manager Portal API
Author: Rob
Version: 1.0.1
Author URI: http://footballmanagerportal.co.uk
*/
function fmp_add_post($post, $api)
{
	$tags = array();

	$wp_cats = wp_get_post_categories($post->ID);

	foreach($wp_cats as $wp_cat_id)
	{
		$wp_cat = get_category($wp_cat_id);

		$tags[] = $wp_cat->name;
	}

	$wp_tags = wp_get_post_tags($post->ID);

	foreach($wp_tags as $wp_tag)
	{
		$tags[] = $wp_tag->name;
	}

	$api->add_content(
		'article',
		get_permalink($post->ID),
		$post->post_title,
		$post->ID,
		$post->post_date_gmt,
		$post->post_content,
		$post->post_excerpt,
		$tags
	);
}


function fmportal_init() {
	if($_SERVER['HTTP_USER_AGENT'] == 'fmportal')
	{
		include( plugin_dir_path( __FILE__ ) . 'fmpapi.php');

		$fmpapi = new fmportal_api();

		$request = $_POST;

		if(isset($request['uid']))
		{
			$post = get_post($request['uid']);

			fmp_add_post($post, $fmpapi);

			$fmpapi->send_output();
		}
		else
		{
			$wp_request = array(
				'numberposts'	=>	isset($request['count']) ? $request['count'] : 10,
				'orderby'		=>	'post_date',
				'order'			=>	isset($request['order_dir']) ? $request['order_dir'] : 'desc',
				'post_status'	=> 'publish'
			);

			if($request['orderby'])
			{
				switch($request['orderby'])
				{
					case 'date_created':
						$wp_request['orderby'] = 'post_date';
					break;
					case 'date_modified':
						$wp_request['orderby'] = 'post_modified';
					break;
				}
			}

			if($request['offset']) $wp_request['offset'] = $request['offset'];
			if($request['orderby']) $wp_request['orderby'] = $request['orderby'];

			$posts = get_posts($wp_request);

			foreach($posts as $post)
			{
				fmp_add_post($post, $fmpapi);
			}

			$fmpapi->send_output();
		}
	}
}

add_action( 'init', 'fmportal_init' );
?>