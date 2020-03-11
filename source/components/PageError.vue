<template>
	<div class="page --error">

		<div class="content">
			<h1>{{__("It looks like your site didn't transfer.", 'bluehost-move')}}</h1>

			<p>
				{{__("We might have gotten disconnected, or there could be something else going on. Let's figure it out.", 'bluehost-move')}}
			</p>

			<p>
				{{__("Call us at 800-XXX-XXXX", 'bluehost-move')}}
			</p>

			<router-link class="button" to="/compatible" tag="button">{{__("Try Again", 'bluehost-move')}}</router-link>
		</div>

		<img v-bind:src="imageSrc"/>

		<div class="footer"></div>

	</div>
</template>

<script>
	export default {
		data() {
			return {
				imageSrc: window.BHMove.pluginUrl + require('@/images/moving-truck-with-cones.svg').default
			}
		}
	}
</script>
