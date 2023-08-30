import { useEffect, useState } from '@wordpress/element';
import { useNavigate } from 'react-router-dom';
import { TransferProgressIndicator } from '../common/TransferProgressIndicator';
import { useInterval } from '../../utils/hooks';
import { apiCall } from '../../utils/apiCall';
import { SiteMigratorAPIs } from '../../utils/api';
import { LoadingButton } from '../common/LoadingButton';

export const TransferStatus = () => {
	const initialStatus = {
		message: 'Preparing environment for packaging',
		progress: 2,
		stage: 'initial',
		packagedFailed: false,
		packagedSuccess: false,
	};
	const [ transferStatus, setTransferStatus ] = useState( initialStatus );
	const [ loading, setLoading ] = useState( false );
	const navigate = useNavigate();

	const getTransferStatus = async () => {
		const status = await apiCall( {
			apiCallFunc: SiteMigratorAPIs().migrationTasks.getTransferStatus,
		} );
		if ( ! status.status || status.status?.length === 0 ) {
			setTransferStatus( initialStatus );
			return;
		}
		setTransferStatus( {
			message: status?.status?.message,
			progress: status?.status?.progress,
			stage: status?.status?.stage,
			packagedFailed: status?.packaged_failed,
			packagedSuccess: status?.packaged_success,
		} );
	};

	// Ping every 5 seconds
	useInterval(
		() => {
			getTransferStatus();
		},
		transferStatus.packagedFailed || transferStatus.packagedSuccess
			? null
			: 5000
	);

	const reportAndMoveOn = async ( failed = false ) => {
		if ( failed ) {
			await apiCall( {
				apiCallFunc: SiteMigratorAPIs().migrationTasks.reportFailed,
			} );
			navigate( '/error' );
			return;
		}
		const response = await apiCall( {
			apiCallFunc:
				SiteMigratorAPIs().migrationTasks.sendPackagedFilesDetails,
		} );
		if ( response.failed || ! response.success ) {
			navigate( '/error' );
			return;
		}
		window.location.reload();
	};

	useEffect( () => {
		if ( transferStatus.packagedFailed ) {
			reportAndMoveOn( true );
		}
		if ( transferStatus.packagedSuccess ) {
			reportAndMoveOn();
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ transferStatus ] );

	const onCancel = async () => {
		setLoading( true );
		await apiCall( {
			apiCallFunc: SiteMigratorAPIs().migrationTasks.cancelTransfer,
		} );
		setTimeout( () => {
			setLoading( false );
			window.location.reload();
		}, 3000 );
	};

	return (
		<div className="transfer-status-div">
			<h1
				className="text-5xl text-center font-bold pt-14"
				id="transfer-status-heading"
			>
				Cloning your website
			</h1>
			<div className="flex justify-center mt-4">
				<p className="text-center text-lg mt-6 w-2/5">
					Please wait for the cloning process to complete, once
					completed, we will issue you your transfer key
				</p>
			</div>
			<div className="flex justify-center mt-4 px-10">
				<TransferProgressIndicator
					progress={ transferStatus.progress }
					message={ transferStatus.message }
				/>
			</div>
			<div className="flex justify-center mt-1">
				<LoadingButton
					id="cancel-transfer-button"
					onSubmit={ onCancel }
					loading={ loading }
				>
					Cancel Transfer
				</LoadingButton>
			</div>
		</div>
	);
};
