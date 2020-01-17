<template>
	<div class="page --incompatible">

		<div class="content">

			<h1>That didn't work:<br> Let's bring in the pros.</h1>

			<p>
				This can happen if you have a multisite, alternate directory structures, or certain themes or plugins.
				The transfer might require some extra steps, or we might need to look at other options.
			</p>

			<p>Give us a call at 800-XXX-XXXX.</p>

		</div>

		<img v-bind:src="imageSrc"/>

		<div class="footer"></div>

	</div>
</template>

<script>
	export default {
		data() {
			return {
				imageSrc: window.BHMove.pluginUrl + require('@/images/computer-transfer-broken.svg').default
			}
		}
	}
</script>
