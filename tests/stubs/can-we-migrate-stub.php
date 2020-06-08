<?php

add_filter(
	'pre_http_request',
	function ( $preempt, $args, $url ) {
		if ( 'https://cwm.eigproserve.com/api/v1/ManifestScan' === $url ) {
			return array(
				'headers'  => [],
				'body'     => json_encode(
					array(
						'migrationId' => 'm1gr@t10n1d',
						'feasible'    => true,
					)
				),
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
