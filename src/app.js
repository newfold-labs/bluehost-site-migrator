import { Alert, Root } from '@newfold/ui-component-library';

function App() {
	return (
		<Root context={ { isRtl: false } }>
			<Alert variant="success">
				Congratulations you have setup the UI library
			</Alert>
		</Root>
	);
}

export default App;
