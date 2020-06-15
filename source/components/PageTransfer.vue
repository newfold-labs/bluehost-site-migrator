<template>
	<div class="page --transfer">
		<div class="content">
			<h2>{{__("Transferring your website", 'bluehost-site-migrator')}}</h2>
			<div class="modal">
				<p>{{message}}</p>
				<ProgressBar :isAnimated="true" :progressPercentage="progressPercentage"/>
				<router-link class="button-secondary" to="/compatible" tag="button">{{__("Cancel Transfer",
					'bluehost-site-migrator')}}
				</router-link>
			</div>
		</div>
		<img v-bind:src="imageSrc"/>
		<div class="footer"></div>
	</div>
</template>

<script>
	import ProgressBar from '@/components/ProgressBar.vue';
	import apiFetch from '@wordpress/api-fetch';
	import {__, sprintf} from '@wordpress/i18n';

	apiFetch.use(apiFetch.createNonceMiddleware(window.BHSiteMigrator.restNonce));
	apiFetch.use(apiFetch.createRootURLMiddleware(window.BHSiteMigrator.restRootUrl));

	// TODO: Base default progress bar progress on PHP timeout?

	export default {
		components: {ProgressBar},
		data() {
			return {
				imageSrc: window.BHSiteMigrator.pluginUrl + require('@/images/boxed-up.svg').default,
				isComplete: null,
				message: __('Preparing to generate package files...', 'bluehost-site-migrator'),
				progressPercentage: 100,
				packages: null,
			}
		},
		methods: {
			async isValidPackage(packageType) {
				return await apiFetch({path: `/bluehost-site-migrator/v1/migration-package/${packageType}/is-valid`});
			},
			fetchExistingMigrationPackages() {
				apiFetch({path: '/bluehost-site-migrator/v1/migration-package'})
					.catch((error) => {
						console.error(error);
						this.$router.push('/error');
					})
					.catch((error) => {
						console.error(error);
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
				this.message = sprintf(__('Packaging %s...', 'bluehost-site-migrator'), packageType);
				return await apiFetch({
					method: 'POST',
					path: `/bluehost-site-migrator/v1/migration-package/${packageType}`
				})
					.catch((error) => {
						this.$router.push('/error');
						throw error;
					})
					.then((packageData) => {
						this.packages[packageType] = packageData;
					});
			},
			sendUpdatedManifestFile() {
				apiFetch({
					method: 'POST',
					path: '/bluehost-site-migrator/v1/manifest/send'
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
