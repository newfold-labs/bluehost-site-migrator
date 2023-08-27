import { useEffect, useState } from '@wordpress/element';
import { LoadingSpinner } from './common/LoadingSpinner';
import { BeginTransfer } from './transfer/BeginTransfer';
import { apiCall } from '../utils/apiCall';
import { SiteMigratorAPIs } from '../utils/api';
import { CompatibilityCheck } from './compatibility/Check';
import { TransferStatus } from './transfer/TransferStatus';
import { TransferSuccess } from './transfer/TransferSuccess';

// The base component that loads the required step based on current migration state
export const Migration = () => {
	const [ stepResult, setStepResult ] = useState( {
		loading: true,
		compatible: false,
		checked: false,
		failed: false,
		error: '',
		transferQueued: false,
	} );

	useEffect( () => {
		const getCurrentStep = async () => {
			const response = await apiCall( {
				apiCallFunc: SiteMigratorAPIs().migrationCheck.getCurrentStep,
			} );
			if ( response.failed ) {
				setStepResult( {
					loading: false,
					error: response.error,
					failed: true,
				} );
			}
			setStepResult( {
				loading: false,
				compatible: response.compatible,
				checked: response.checked,
				transferQueued: response.transfer_queued,
				packagedSuccess: response.packaged_success,
				packagedFailed: response.packaged_failed,
			} );
		};

		getCurrentStep();
	}, [] );

	if ( stepResult.loading ) {
		return <LoadingSpinner />;
	}

	if ( stepResult.packagedSuccess ) {
		return <TransferSuccess />;
	}

	if ( stepResult.packagedFailed ) {
		return <></>;
	}

	if ( stepResult.transferQueued ) {
		return <TransferStatus />;
	}

	if ( stepResult.compatible ) {
		return <BeginTransfer />;
	}

	if ( ! stepResult.checked ) {
		return <CompatibilityCheck />;
	}
};
