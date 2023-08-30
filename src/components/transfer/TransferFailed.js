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
					It looks like your site didn&apos;t transfer.
				</h1>
			</div>
			<div className="flex justify-center mt-4">
				<p className="text-center text-lg mt-6 w-3/5">
					We might have gotten disconnected , or there could be
					something else going on. Let&apos;s figure it out.
				</p>
			</div>
			<div className="flex justify-center mt-4">
				<p className="text-center text-lg mt-6 w-3/5">
					Call us at 888-401-4678
				</p>
			</div>
			<div className="flex justify-center mt-1">
				<LoadingButton
					id="retry-transfer-button"
					onSubmit={ onRetry }
					loading={ loading }
				>
					Try again
				</LoadingButton>
			</div>
		</div>
	);
};
