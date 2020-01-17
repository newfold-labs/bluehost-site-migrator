<template>
	<div class="page --error">

		<div class="content">
			<h1>It looks like your site didn't transfer.</h1>

			<p>
				We might have gotten disconnected, or there could be something else going on. Let's figure it out.
			</p>

			<p>
				Call us at 800-XXX-XXXX
			</p>

			<router-link class="button" to="/compatible" tag="button">Try Again</router-link>
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
