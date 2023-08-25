import { useState } from '@wordpress/element';
import { ProgressBar } from '../common/ProgressBar';

export const TransferStatus = () => {
	const [ progress ] = useState( 10 );

	return (
		<div className="h-full bg-white">
			<div className="transfer-status-div mt-14">
				<h1 className="text-5xl text-center font-bold">
					Cloning your website
				</h1>
				<div className="flex justify-center mt-4">
					<p className="text-center text-lg mt-6 w-2/5">
						Please wait for the cloning process to complete, once
						completed, we will issue you your transfer key
					</p>
				</div>
				<div className="flex justify-center mt-4 px-10">
					<ProgressBar
						progress={ progress }
						message={ 'Packaging plugins' }
					/>
				</div>
			</div>
		</div>
	);
};
