import { useRoutes } from 'react-router-dom';
import { Migration } from './components/Migration';

export default function Routes() {
	return useRoutes( [
		{
			path: '/',
			element: <Migration />,
		},
	] );
}
