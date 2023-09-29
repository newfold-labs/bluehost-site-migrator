import { useEffect, useState } from '@wordpress/element';
import { LoadingSpinner } from './common/LoadingSpinner';
import { BeginTransfer } from './transfer/BeginTransfer';
import { apiCall } from '../utils/apiCall';
import { SiteMigratorAPIs } from '../utils/api';
import { CompatibilityCheck } from './compatibility/Check';
import { TransferStatus } from './transfer/TransferStatus';
import { TransferSuccess } from './transfer/TransferSuccess';
import { useNavigate } from 'react-router-dom';

// The base component that loads the required step based on current migration state
export const Migration = () => {
	const [ stepResult, setStepResult ] = useState( {
		loading: true,
		compatible: false,
		checked: false,
		failed: false,
		error: '',
		transferQueued: false,
		cancelled: false,
	} );

	const navigate = useNavigate();

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
				cancelled: response.cancelled,
			} );
		};

		getCurrentStep();
	}, [] );

	if ( stepResult.loading ) {
		return <LoadingSpinner />;
	}

	if ( stepResult.packagedFailed ) {
		navigate( '/error' );
	}

	if ( stepResult.packagedSuccess ) {
		return <TransferSuccess />;
	}

	if ( stepResult.transferQueued ) {
		return <TransferStatus />;
	}

	if ( stepResult.compatible ) {
		return <BeginTransfer cancelled={ stepResult.cancelled } />;
	}

	if ( ! stepResult.checked ) {
		return <CompatibilityCheck />;
	}

	if ( ! stepResult.compatible ) {
		navigate( '/incompatible' );
	}
};
