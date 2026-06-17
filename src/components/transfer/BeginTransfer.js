import { __ } from '@wordpress/i18n';

import { useNavigate } from 'react-router-dom';
import { useEffect, useState } from '@wordpress/element';
import { LoadingSpinner } from '../common/LoadingSpinner';
import { LoadingButton } from '../common/LoadingButton';
import { apiCall } from '../../utils/apiCall';
import { SiteMigratorAPIs } from '../../utils/api';

export const BeginTransfer = ( { cancelled = false } ) => {
	const [ loading, setLoading ] = useState( true );
	const navigate = useNavigate();

	const beginTransfer = async () => {
		setLoading( true );
		const response = await apiCall( {
			apiCallFunc: SiteMigratorAPIs().migrationTasks.queueMigrationTasks,
		} );
		if ( response.failed ) {
			setLoading( false );
			navigate( '/error', {
				state: {
					error: response.error || 'Unknown error',
					step: 'queueTasks',
				},
			} );
		}
		if ( response.queued ) {
			// Reload after a certain delay to allow propagating the status
			setTimeout( () => {
				window.location.reload();
				setLoading( false );
			}, 3000 );
		}
	};

	useEffect( () => {
		if ( ! cancelled ) {
			beginTransfer();
		} else {
			setLoading( false );
		}
	}, [] );

	if ( loading ) {
		return <LoadingSpinner />;
	}

	return (
		<div className="h-full bg-white">
			<div className="transfer-start-div">
				<div className="pt-14 pl-12">
					<h1 className="text-5xl font-bold">
						{ __(
							"Look's like we're compatible!",
							'bluehost-site-migrator'
						) }
					</h1>
					<p className="text-lg mt-6 w-2/5">
						{ __(
							`We need to make a clone of your Wordpress website next,
							which we can then use to transfer you to your desired
						account.`,
							'bluehost-site-migrator'
						) }
						<br />
						<br />
						{ __(
							`Please wait to make changes to your website until the
						transfer process has been completed. In addition, for
						now, leave your DNS and domain settings the same as
						well.`,
							'bluehost-site-migrator'
						) }
						<br />
						<br />
						{ __(
							`Once the cloning process is completed, you will be given
						a transfer key that can be copied and used to complete
						the WordPress website transfer process.`,
							'bluehost-site-migrator'
						) }
					</p>
					<LoadingButton
						loading={ loading }
						id="begin-transfer-button"
						onSubmit={ beginTransfer }
					>
						{ __( 'Start Transfer', 'bluehost-site-migrator' ) }
					</LoadingButton>
				</div>
			</div>
		</div>
	);
};
