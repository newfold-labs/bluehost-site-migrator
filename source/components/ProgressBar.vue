<template>
	<div v-bind:class="{'progress-bar': true, '--animated': this.isAnimated}">
		<div class="progress-bar__progress" v-bind:style="{width: this.progressPercentage + '%'}"></div>
	</div>
</template>

<script>
	export default {
		props: {
			isAnimated: Boolean,
			progressPercentage: Number,
		}
	}
</script>
