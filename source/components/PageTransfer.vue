<template>
	<div class="page --transfer">
		<div class="content">
			<h2>Transferring your website</h2>
			<div class="modal">
				<p>{{message}}</p>
				<ProgressBar :progressPercentage="progressPercentage"/>
				<router-link class="button-secondary" to="/compatible" tag="button">Cancel Transfer</router-link>
			</div>
		</div>
		<img v-bind:src="imageSrc"/>
		<div class="footer"></div>
	</div>
</template>

<script>
	import ProgressBar from '@/components/ProgressBar.vue';
	import apiFetch from '@wordpress/api-fetch';

	apiFetch.use(apiFetch.createNonceMiddleware(window.BHMove.restNonce));
	apiFetch.use(apiFetch.createRootURLMiddleware(window.BHMove.restRootUrl));

	// TODO: Base default progress bar progress on PHP timeout?

	export default {
		components: {ProgressBar},
		data() {
			return {
				imageSrc: window.BHMove.pluginUrl + require('@/images/boxed-up.svg').default,
				isComplete: null,
				message: 'Preparing to generate package files...',
				progressPercentage: 100,
				packages: null,
			}
		},
		methods: {
			async isValidPackage(packageType) {
				return await apiFetch({path: `/bluehost-move/v1/migration-package/${packageType}/is-valid`});
			},
			fetchExistingMigrationPackages() {
				apiFetch({path: '/bluehost-move/v1/migration-package'})
					.catch((error) => {
						console.error(error);
						this.$router.push('/error');
					})
					.then(async (packages) => {
						// Store existing packages
						this.packages = packages;
						// Generate packages that are missing
						for (const packageType in packages) {
							if (!await this.isValidPackage(packageType)) {
								await this.generateMigrationPackage(packageType);
							}
						}
						this.sendUpdatedManifestFile();
					});
			},
			async generateMigrationPackage(packageType) {
				this.message = 'Packaging ' + packageType + '...';
				await apiFetch({
					method: 'POST',
					path: `/bluehost-move/v1/migration-package/${packageType}`
				})
					.catch((error) => {
						console.error(error);
						this.$router.push('/error');
					})
					.then((packageData) => {
						this.packages[packageType] = packageData;
					});
			},
			sendUpdatedManifestFile() {
				apiFetch({
					method: 'POST',
					path: '/bluehost-move/v1/manifest/send'
				})
					.catch((error) => {
						console.error(error);
						this.$router.push('/error');
					})
					.then(() => {
						this.isComplete = true;
					});
			},
		},
		watch: {
			isComplete(isComplete) {
				this.$router.push(isComplete ? '/complete' : '/error');
			},
		},
		mounted() {
			this.fetchExistingMigrationPackages();
		},
	}
</script>
