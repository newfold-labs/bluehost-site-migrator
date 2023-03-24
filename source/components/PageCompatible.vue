<template>
	<div class="page --compatible">
		<div class="content">
			<h1>{{ __("Looks like we're compatible!", 'bluehost-site-migrator') }}</h1>
			<p>{{ __("We need to make a clone of your WordPress website next, which we can then use to transfer to your desired account.", 'bluehost-site-migrator') }}</p>
			<p>
				{{ __("Please wait to make changes to your website until the transfer process has been completed. In addition, for now, leave your DNS and domain settings the same as well.", 'bluehost-site-migrator') }}
			</p>
			<p>{{ __("Once the cloning process is completed, you will be given a transfer key that can be copied and used to complete the WordPress website transfer process.", 'bluehost-site-migrator') }}</p>
			<router-link class="button" to="/transfer" tag="button">{{__("Start Transfer", 'bluehost-site-migrator')}}</router-link>
		</div>
		<img v-bind:src="imageSrc"/>
		<div class="footer"></div>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				imageSrc: require('@/images/computer-transfer.svg')
			}
		}
	}
</script>
