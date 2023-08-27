export const TransferSuccess = () => {
	return (
		<div className="transfer-success-div">
			<div className="flex justify-center mt-3">
				<h1 className="text-5xl text-center font-bold  w-2/5">
					Great news, your website has been cloned successfully!
				</h1>
			</div>
			<div className="flex justify-center mt-4">
				<p className="text-center text-lg mt-6 w-2/5">
					Your site has been cloned and is now ready for transfer. To
					initiate the transfer, you need to copy the transfer key and
					paste it into the Migration Services page.
				</p>
			</div>
			<div className="flex justify-center mt-16">
				<h2 className="text-2xl text-center">1KTY-60G5-6767-BH5H</h2>
			</div>
			<div className="flex justify-center mt-1">
				<button className="action-button">Copy transfer key</button>
			</div>
			<div className="flex justify-center mt-4">
				<a
					href="https://google.com"
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
						href="https://google.com"
						target="_blank"
						className="text-lg text-[#3575D3] underline "
						rel="noreferrer"
					>
						Create account
					</a>
				</p>
			</div>
		</div>
	);
};
