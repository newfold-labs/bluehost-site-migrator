<?php

add_filter(
	'pre_http_request',
	function ( $preempt, $args, $url ) {
		if ( 'https://cwm.eigproserve.com/api/v1/migration/' . get_option( 'bh_site_migration_id' ) . '/files' === $url ) {
			return array(
				'headers'  => [],
				'body'     => '',
				'response' => array(
					'code' => 200,
				),
			);
		}

		return $preempt;
	},
	10,
	3
);
