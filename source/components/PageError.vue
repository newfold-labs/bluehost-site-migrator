<template>
	<div class="page --error">

		<div class="content">
			<h1>{{__("It looks like your site didn't transfer.", 'bluehost-site-migrator')}}</h1>

			<p>
				{{__("We might have gotten disconnected, or there could be something else going on. Let's figure it out.", 'bluehost-site-migrator')}}
			</p>

			<p>
				{{__("Call us at 888-401-4678", 'bluehost-site-migrator')}}
			</p>

			<router-link class="button" to="/compatible" tag="button">{{__("Try Again", 'bluehost-site-migrator')}}</router-link>
		</div>

		<img v-bind:src="imageSrc"/>

		<div class="footer"></div>

	</div>
</template>

<script>
	export default {
		data() {
			return {
				imageSrc: require('@/images/moving-truck-with-cones.svg')
			}
		}
	}
</script>
