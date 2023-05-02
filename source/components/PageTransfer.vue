<template>
	<div class="page --transfer">
		<div class="content">
			<h1>{{__("Cloning your website", 'bluehost-site-migrator')}}</h1>
			<p>{{ __("Please wait for the cloning process to complete, once completed, we ", 'bluehost-site-migrator') }}</p>
			<p>{{ __("will issue you your transfer key.", 'bluehost-site-migrator') }}</p>
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

	export default {
		components: {ProgressBar},
		data() {
			return {
				imageSrc: require('@/images/boxed-up.svg'),
				isComplete: null,
				message: __('Preparing to generate package files...', 'bluehost-site-migrator'),
				progressPercentage: 100,
				packages: null,
			}
		},
		methods: {
			async sleep(ms) {
				return new Promise(resolve => setTimeout(resolve, ms));
			},
			async isValidPackage(packageType) {
				return await apiFetch({path: `/bluehost-site-migrator/v1/migration-package/${packageType}/is-valid`});
			},
			async isPackageScheduled(packageType) {
				return await apiFetch({path: `/bluehost-site-migrator/v1/migration-package/${packageType}/is-scheduled`});
			},
			async queuePackagingTasks() {
				await apiFetch({ path: '/bluehost-site-migrator/v1/migration-package/queue-tasks' });
			},
			async sendErrorLogs() {
				try {
					await apiFetch({
						method: 'POST',
						path: '/bluehost-site-migrator/v1/manifest/report-errors'
					});
				} catch (error) {
					console.log(error);
				}
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
							this.message = sprintf(__('Packaging %s...', 'bluehost-site-migrator'), packageType);
							let timeInterval = 3000;
							let success = await this.isValidPackage(packageType);
							while (!success) {
								try {
									timeInterval += 5000;
									success = await this.isValidPackage(packageType);
									const scheduled = await this.isPackageScheduled(packageType);
									if (!scheduled) {
										// Break the loop and redirect to failed state
										await this.sendErrorLogs();
										this.$router.push('/error');
										success = false;
										return;
									}
									await fetch('/wp-cron.php');
									await this.sleep(timeInterval);
								} catch (exception) {
									console.log(exception);
								}
							}
						}
						this.sendUpdatedManifestFile();
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
			this.queuePackagingTasks();
			this.fetchExistingMigrationPackages();
		},
	}
</script>
