import { useRoutes } from 'react-router-dom';
import { Migration } from './components/Migration';
import { TransferFailed } from './components/transfer/TransferFailed';

export default function Routes() {
	return useRoutes( [
		{
			path: '/',
			element: <Migration />,
		},
		{
			path: '/error',
			element: <TransferFailed />,
		},
	] );
}
