<template>
	<div class="page --compatible">
		<div class="content">
			<h1>Looks like we're<br/> compatible!</h1>
			<p>Next, let's transfer your site.</p>
			<p>
				Please wait to make changes to your website until the transfer is completely done. For now, leave your
				DNS and domain the same as well.
			</p>
			<router-link class="button" to="/transfer" tag="button">Start Transfer</router-link>
		</div>
		<img v-bind:src="imageSrc"/>
		<div class="footer"></div>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				imageSrc: window.BHMove.pluginUrl + require('@/images/computer-transfer.svg').default
			}
		}
	}
</script>
