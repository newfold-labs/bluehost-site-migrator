import { __ } from '@wordpress/i18n';

export const Incompatible = () => {
	return (
		<div className="h-full bg-white">
			<div className="incompatible-div">
				<div className="pt-14 pl-12">
					<h1 className="text-5xl font-bold">
						{ __(
							"That didn't work: Let's bring in the pros.",
							'bluehost-site-migrator'
						) }
					</h1>
					<p className="text-lg mt-6 w-2/5">
						{ __(
							'This can happen if you have a multisite, alternate '.concat(
								'directory structures, or certain themes or plugins. The ',
								'transfer might require some extra steps, or we might ',
								'need to look at other options.'
							),
							'bluehost-site-migrator'
						) }
						<br />
						<br />
						{ __(
							'Give us a call at 888-401-4678.',
							'bluehost-site-migrator'
						) }
					</p>
				</div>
			</div>
		</div>
	);
};
