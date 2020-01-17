<template>
	<div class="page --check-compatibility">
		<div class="content">
			<h1>Bluehost Site Migrator</h1>
			<p><strong>Let's get this truck rolling:</strong></p>
			<ul>
				<li>First we'll check to see if your website is compatible.</li>
				<li>If it's compatible, we'll transfer your site.</li>
				<li>And then send you a link for review.</li>
			</ul>
			<button v-on:click="checkCompatibility" v-bind:class="this.buttonClasses">
				Check Compatibility
				<spinner color="white" v-bind:is-visible="this.isSpinnerVisible" size="18"/>
			</button>
			<span class="message">{{message}}</span>
		</div>
		<img v-bind:src="imageSrc"/>
		<div class="footer"></div>
	</div>
</template>

<script>
	import Spinner from "./Spinner.vue";
	import apiFetch from '@wordpress/api-fetch';

	apiFetch.use(apiFetch.createNonceMiddleware(window.BHMove.restNonce));
	apiFetch.use(apiFetch.createRootURLMiddleware(window.BHMove.restRootUrl));

	export default {
		components: {Spinner},
		data() {
			return {
				imageSrc: window.BHMove.pluginUrl + require('@/images/moving-truck-loaded.svg').default,
				isButtonDisabled: false,
				isCompatible: null,
				isSpinnerVisible: false,
				message: ''
			}
		},
		computed: {
			buttonClasses() {
				return 'button' + (this.isButtonDisabled ? ' --is-disabled' : '');
			}
		},
		methods: {
			checkCompatibility() {
				this.isSpinnerVisible = true;
				this.isButtonDisabled = true;
				this.loopMessages();
				apiFetch({path: '/bluehost-move/v1/can-we-migrate'})
					.catch((error) => {
						console.error(error);
						this.$router.push('/error');
					})
					.then((isCompatible) => {
						this.isCompatible = isCompatible;
					});
			},
			loopMessages() {
				const messages = [
					'Checking environment...',
					'Checking plugins...',
					'Checking themes...',
					'Checking configuration...',
				];
				messages.forEach((msg, index) => {
					setTimeout(() => {
						this.message = msg;
					}, 5000 * index);
				});
			}
		},
		watch: {
			isCompatible(value) {
				this.$router.push(value ? '/compatible' : '/incompatible');
			}
		}
	}
</script>
