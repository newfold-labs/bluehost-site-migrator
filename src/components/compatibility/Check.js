import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useNavigate } from 'react-router-dom';
// UI Imports
import { LoadingButton } from '../common/LoadingButton';
// Utils
import { apiCall } from '../../utils/apiCall';
import { SiteMigratorAPIs } from '../../utils/api';

export const CompatibilityCheck = () => {
	const [ loading, setLoading ] = useState( false );
	const navigate = useNavigate();

	const onCheckSubmit = async () => {
		setLoading( true );
		const compatibility = await apiCall( {
			apiCallFunc: SiteMigratorAPIs().migrationCheck.runMigrationChecks,
		} );
		setLoading( false );
		if ( compatibility.failed || ! compatibility.can_migrate ) {
			navigate( '/incompatible', {
				state: {
					error: compatibility.error || 'Unknown error',
					step: 'migrationCheck',
				},
			} );
			return;
		}
		if ( compatibility.can_migrate ) {
			window.location.reload();
		}
	};

	return (
		<div className="h-full bg-white">
			<div className="compatibility-div">
				<div className="pt-14 pl-12">
					<h1 className="text-5xl font-bold">
						{ __(
							'Bluehost Site Migrator',
							'bluehost-site-migrator'
						) }
					</h1>
					<p className="font-bold text-lg mt-6">
						{ __(
							"Let's get this truck rolling:",
							'bluehost-site-migrator'
						) }
					</p>
					<p className="text-lg mt-6 w-2/5">
						{ __(
							'A website compatibility check needs to be performed '.concat(
								'before the transfer process can begin to verify that ',
								'your website can be transferred'
							),
							'bluehost-site-migrator'
						) }
					</p>
					<LoadingButton
						id="check-compatibility-button"
						onSubmit={ onCheckSubmit }
						loading={ loading }
					>
						Check Compatibility
					</LoadingButton>
				</div>
			</div>
		</div>
	);
};
