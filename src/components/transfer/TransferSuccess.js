import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { useNavigate } from 'react-router-dom';
import { Alert } from '../common/Alert';
import { apiCall } from '../../utils/apiCall';
import { SiteMigratorAPIs } from '../../utils/api';
import { LoadingSpinner } from '../common/LoadingSpinner';

export const TransferSuccess = () => {
	const initialUrls = {
		signupUrl: 'https://www.bluehost.com/hosting/shared#pricing-cards',
		loginUrl: 'https://my.bluehost.com/web-hosting/cplogin',
	};

	const [ migrationId, setMigrationId ] = useState( '1KTY-60G5-6767-BH5H' );
	const [ countryCode, setCountryCode ] = useState( 'US' );
	const [ loading, setLoading ] = useState( true );
	const [ successAlertMessage, setSuccessAlertMessage ] = useState( false );
	const [ successAlertVisible, setSuccessAlertVisible ] = useState( false );
	const [ urls, setUrls ] = useState( initialUrls );
	const [ regions, setRegions ] = useState( {
		US: {
			countryName: 'United States of America',
			...initialUrls,
		},
	} );

	const navigate = useNavigate();

	const copyTransferKey = async () => {
		// eslint-disable-next-line no-undef
		await navigator.clipboard.writeText( migrationId );
		setSuccessAlertMessage( 'Copied transfer key successfully' );
		setSuccessAlertVisible( true );
	};

	const getValidCountryCode = ( countryCodeInput ) => {
		// If country code exists, use it
		if ( regions.hasOwnProperty( countryCodeInput ) ) {
			return countryCode;
		}
		// If country code doesn't exist, use an empty string (if valid)
		if ( regions.hasOwnProperty( '' ) ) {
			return '';
		}
		// Otherwise, default to the first key
		return Object.keys( regions )[ 0 ];
	};

	const setUrlsFromCountryCode = ( receivedCountryCode ) => {
		setUrls( {
			signupUrl:
				regions[ getValidCountryCode( receivedCountryCode ) ].signupUrl,
			loginUrl:
				regions[ getValidCountryCode( receivedCountryCode ) ].loginUrl,
		} );
	};

	const getMigrationData = async () => {
		const response = await apiCall( {
			apiCallFunc: SiteMigratorAPIs().migrationData.getMigrationData,
		} );
		setLoading( false );
		if ( response.failed ) {
			navigate( '/error' );
		}
		setMigrationId( response.migrationId );
		const receivedRegions = {};
		for ( const region of response.regions ) {
			receivedRegions[ region.countryCode ] = {
				countryName: region.countryName,
				signupUrl: region.signupUrl,
				loginUrl: region.loginUrl,
			};
		}
		setRegions( receivedRegions );
		setCountryCode( response.countryCode );
	};

	useEffect( () => {
		getMigrationData();
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	useEffect( () => {
		setUrlsFromCountryCode( countryCode );
		setSuccessAlertVisible( true );
		setSuccessAlertMessage(
			`Country updated to ${ regions[ countryCode ].countryName }`
		);
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ countryCode, regions ] );

	if ( loading ) {
		return <LoadingSpinner />;
	}

	return (
		<div className="transfer-success-div">
			<div className="flex justify-center mt-3">
				<h1 className="text-5xl text-center font-bold  w-2/5">
					Great news, your website has been cloned successfully!
				</h1>
			</div>
			<div className="flex justify-center mt-4">
				<p className="text-center text-lg mt-6 w-2/5">
					{ __(
						`Your site has been cloned and is now ready for transfer. To
					initiate the transfer, you need to copy the transfer key and
					paste it into the Migration Services page.`,
						'bluehost-site-migrator'
					) }
				</p>
			</div>
			<div className="flex justify-center mt-16">
				<h2 id="migration-id" className="text-2xl text-center">
					{ migrationId }
				</h2>
			</div>
			<div className="flex justify-center mt-1">
				<button
					id="copy-transfer-key-button"
					className="action-button"
					onClick={ copyTransferKey }
				>
					Copy transfer key
				</button>
			</div>
			<div className="flex justify-center mt-4">
				<a
					href={ urls.loginUrl }
					target="_blank"
					className="text-lg text-[#3575D3]"
					rel="noreferrer"
				>
					Login to Bluehost
				</a>
			</div>
			<div className="flex justify-center mt-4">
				<p className="text-lg">
					Don&apos;t have an account ?&nbsp;
					<a
						href={ urls.signupUrl }
						target="_blank"
						className="text-lg text-[#3575D3] underline "
						rel="noreferrer"
					>
						Create account
					</a>
				</p>
			</div>
			<div className="flex justify-center mt-4">
				<p className="text-lg">
					Choose your country:
					<select
						className="ml-2"
						value={ countryCode }
						onChange={ ( selectedOption ) => {
							setCountryCode( selectedOption.target.value );
						} }
						id="country-select"
					>
						{ Object.keys( regions ).map( ( regionCountryCode ) => {
							const region = regions[ regionCountryCode ];
							return (
								<option
									value={ regionCountryCode }
									key={ regionCountryCode }
								>
									{ region.countryName }
								</option>
							);
						} ) }
					</select>
				</p>
			</div>
			<Alert
				message={ successAlertMessage }
				visible={ successAlertVisible }
				setVisible={ setSuccessAlertVisible }
			/>
		</div>
	);
};
