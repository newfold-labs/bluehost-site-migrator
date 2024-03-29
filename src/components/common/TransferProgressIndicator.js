import { __ } from '@wordpress/i18n';

export const TransferProgressIndicator = ( { progress, message } ) => {
	return (
		<div className="bg-white mt-5 rounded-xl pt-2 pb-8 py-4 w-2/4">
			<p className="text-center text-xl mb-3 mt-3">
				{ __( message, 'bluehost-site-migrator' ) }
			</p>
			<div className="flex justify-center">
				<div className="animate-pulse rounded-xl w-4/5 bg-neutral-200 dark:bg-neutral-600">
					<div
						className="ease-in duration-300 rounded-xl bg-[#3575D3] h-5 p-0.5 text-center text-xs font-medium leading-none text-primary-100"
						style={ { width: `${ progress }%` } }
					/>
				</div>
			</div>
		</div>
	);
};
