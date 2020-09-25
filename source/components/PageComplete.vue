<template>
	<div class="page --complete">

		<div class="content">

			<h1>{{__("Welcome to the Bluehost family!", 'bluehost-site-migrator')}}</h1>

			<p>
				{{__("You've transferred your website to Bluehost. Now we just need to get it set up on your Bluehost account so you can review it.", 'bluehost-site-migrator')}}
			</p>

			<a class="button"
			   v-bind:href="siteMigrationUrl"
			   target="_blank"
			   rel="noreferrer noopener"
			>
				{{__("Login to Bluehost", 'bluehost-site-migrator')}}
			</a>

			<p>
				<span class="text-disabled">{{__("Don't have an account?", 'bluehost-site-migrator')}}</span>&nbsp;&nbsp;
				<a
					v-bind:href="accountCreationUrl"
					target="_blank"
					rel="noreferrer noopener"
				>
					{{__("Create account", 'bluehost-site-migrator')}}
				</a>
			</p>

			<img v-bind:src="imageSrc"/>

		</div>

		<div class="footer"></div>

	</div>
</template>

<script>
	import apiFetch from '@wordpress/api-fetch';

	apiFetch.use(apiFetch.createNonceMiddleware(window.BHSiteMigrator.restNonce));
	apiFetch.use(apiFetch.createRootURLMiddleware(window.BHSiteMigrator.restRootUrl));

	export default {
		computed: {
			accountCreationUrl() {
				return `https://www.bluehost.com/wordpress-site-migration?migrationId=${this.migrationId}`;
			},
			siteMigrationUrl() {
				return `https://my.bluehost.com/cgi/site_migration/?migrationId=${this.migrationId}`;
			}
		},
		data() {
			return {
				imageSrc: window.BHSiteMigrator.pluginUrl + require('@/images/moving-truck-unloaded.svg').default,
				migrationId: null
			}
		},
		methods: {
			getMigrationId() {
				apiFetch({path: '/bluehost-site-migrator/v1/migration-id'})
					.catch((error) => {
						console.error(error);
						this.$router.push('/error');
					})
					.then((migrationId) => {
						this.migrationId = migrationId;
					});
			}
		},
		mounted() {
			this.getMigrationId();
		}
	}
</script>
