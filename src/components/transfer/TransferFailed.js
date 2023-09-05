import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { LoadingButton } from '../common/LoadingButton';
import { SiteMigratorAPIs } from '../../utils/api';
import { apiCall } from '../../utils/apiCall';
import { useNavigate } from 'react-router-dom';

export const TransferFailed = () => {
	const [ loading, setLoading ] = useState( false );
	const navigate = useNavigate();
	const onRetry = async () => {
		setLoading( true );
		await apiCall( {
			apiCallFunc: SiteMigratorAPIs().migrationTasks.cancelTransfer,
		} );
		setTimeout( () => {
			setLoading( false );
			navigate( '/' );
		}, 3000 );
	};

	return (
		<div className="transfer-success-div">
			<div className="flex justify-center mt-16">
				<h1 className="text-5xl text-center font-bold  w-3/5">
					{ __(
						"It looks like your site didn't transfer.",
						'bluehost-site-migrator'
					) }
				</h1>
			</div>
			<div className="flex justify-center mt-4">
				<p className="text-center text-lg mt-6 w-3/5">
					{ __(
						`We might have gotten disconnected , or there could be
					something else going on. Let's figure it out.`,
						'bluehost-site-migrator'
					) }
				</p>
			</div>
			<div className="flex justify-center mt-4">
				<p className="text-center text-lg mt-6 w-3/5">
					{ __(
						'Call us at 888-401-4678',
						'bluehost-site-migrator'
					) }
				</p>
			</div>
			<div className="flex justify-center mt-1">
				<LoadingButton
					id="retry-transfer-button"
					onSubmit={ onRetry }
					loading={ loading }
				>
					{ __( 'Try again', 'bluehost-site-migrator' ) }
				</LoadingButton>
			</div>
		</div>
	);
};
