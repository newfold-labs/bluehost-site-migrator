<template>
	<div class="page --incompatible">

		<div class="content">

			<h1>{{__("That didn't work:<br> Let's bring in the pros.", 'bluehost-move')}}</h1>

			<p>
				{{__("This can happen if you have a multisite, alternate directory structures, or certain themes or plugins. The transfer might require some extra steps, or we might need to look at other options.", 'bluehost-move')}}
			</p>

			<p>{{__("Give us a call at 800-XXX-XXXX.", 'bluehost-move')}}</p>

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
