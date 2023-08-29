import { useState } from '@wordpress/element';
import { LoadingButton } from '../common/LoadingButton';
import { apiCall } from '../../utils/apiCall';
import { SiteMigratorAPIs } from '../../utils/api';
import { useNavigate } from 'react-router-dom';

export const BeginTransfer = () => {
	const [ loading, setLoading ] = useState( false );
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
				setLoading( false );
				window.location.reload();
			}, 3000 );
		}
	};

	return (
		<div className="h-full bg-white">
			<div className="transfer-start-div">
				<div className="pt-14 pl-12">
					<h1 className="text-5xl font-bold">
						Look&apos;s like we&apos;re compatible!
					</h1>
					<p className="text-lg mt-6 w-2/5">
						We need to make a clone of your Wordpress website next,
						which we can then use to transfer you to your desired
						account. <br />
						<br />
						Please wait to make changes to your website until the
						transfer process has been completed. In addition, for
						now, leave your DNS and domain settings the same as
						well. <br />
						<br />
						Once the cloning process is completed, you will be given
						a transfer key that can be copied and used to complete
						the WordPress website transfer process.
					</p>
					<LoadingButton
						loading={ loading }
						onSubmit={ beginTransfer }
					>
						Start Transfer
					</LoadingButton>
				</div>
			</div>
		</div>
	);
};
