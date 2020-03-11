import Vue from 'Vue';
import VueRouter from 'vue-router';

import App from '@/components/App.vue';
import routes from '@/js/routes';

import {__, _x, _nx, sprintf} from '@wordpress/i18n';

Vue.use(VueRouter);

Vue.mixin({
	methods: {
		__,
		_x,
		_nx,
		sprintf
	}
});

const router = new VueRouter({routes});

window.BHMove.App = new Vue({
	el: '#bluehost-move',
	data: {
		isCompatible: window.BHMove.isCompatible || null,
		isComplete: window.BHMove.isComplete || null,
	},
	created() {

		// Initial page navigation
		if (this.isComplete === null) {
			switch (this.isCompatible) {
				case '1':
				case true:
					this.$router.push('/compatible');
					break;
				case '0':
				case false:
					this.$router.push('/incompatible');
					break;
			}
		} else {
			switch (this.isComplete) {
				case '1':
				case true:
					this.$router.push('/complete');
					break;
				case '0':
				case false:
					this.$router.push('/error');
					break;
			}
		}

	},
	render: h => h(App),
	router
});
