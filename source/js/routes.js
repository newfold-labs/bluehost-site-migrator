import PageCheckCompatibility from '@/components/PageCheckCompatibility.vue';
import PageIncompatible from '@/components/PageIncompatible.vue';
import PageCompatible from '@/components/PageCompatible.vue';
import PageTransfer from '@/components/PageTransfer.vue';
import PageComplete from '@/components/PageComplete.vue';
import PageError from '@/components/PageError.vue';

const routes = [
	{
		path: '/',
		name: 'PageCheckCompatibility',
		component: PageCheckCompatibility
	},
	{
		path: '/incompatible',
		name: 'PageIncompatible',
		component: PageIncompatible
	},
	{
		path: '/compatible',
		name: 'PageCompatible',
		component: PageCompatible
	},
	{
		path: '/transfer',
		name: 'PageTransfer',
		component: PageTransfer
	},
	{
		path: '/complete',
		name: 'PageComplete',
		component: PageComplete
	},
	{
		path: '/error',
		name: 'PageError',
		component: PageError
	},
];

export default routes;
