<template>
	<div class="progress-bar">
		<div class="progress-bar__progress" v-bind:style="{width: this.progressPercentage + '%'}"></div>
	</div>
</template>

<script>
	export default {
		props: {
			progressPercentage: Number,
		}
	}
</script>
