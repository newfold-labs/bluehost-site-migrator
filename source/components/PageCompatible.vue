<template>
	<div class="page --compatible">
		<div class="content">
			<h1>{{__("Looks like we're compatible!", 'bluehost-site-migrator')}}</h1>
			<p>{{__("Next, let's transfer your site.", 'bluehost-site-migrator')}}</p>
			<p>
				{{__("Please wait to make changes to your website until the transfer is completely done. For now, leave your DNS and domain the same as well.", 'bluehost-site-migrator')}}
			</p>
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
