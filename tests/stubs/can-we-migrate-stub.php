<?php

add_filter(
	'pre_http_request',
	function ( $preempt, $args, $url ) {
		if ( 'https://cwm.eigproserve.com/api/v1/ManifestScan' === $url ) {
			return array(
				'headers'  => [],
				'body'     => json_encode(
					array(
						'migrationId'  => 'm1gr@t10n1d',
						'feasible'     => true,
						'x-auth-token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJfaWQiOiI1ZWRlYWEzNWIyZDMwNDM1ZmQ3YjQ1YTgiLCJ1cmwiOiJkb250LnBzeWNoby1hbmFseXplLm1lIiwic2l0ZVR5cGUiOiJ3b3JkcHJlc3MiLCJmZWFzaWJsZSI6dHJ1ZSwiY3JlYXRlZEF0IjoiMjAyMC0wNi0wOFQyMToxNDoyOS45NzFaIiwidXBkYXRlZEF0IjoiMjAyMC0wNi0wOFQyMToxNzoyMy44MDVaIiwiaWF0IjoxNTkxNzIxMzYwLCJleHAiOjE1OTE3MzU3NjB9.AGA_K-lnWBpgFgMyYCrHIDBMPz2OOpbq5eIRK0sBlIA',
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
