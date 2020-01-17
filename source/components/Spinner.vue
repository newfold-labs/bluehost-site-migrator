<template>
	<div class="bluehost-spinner" :style="style"></div>
</template>

<script>
	export default {
		props: {
			color: {
				type: String,
				default: '#525252'
			},
			isVisible: {
				type: [Boolean, String],
				default: true
			},
			size: {
				type: [Number, String],
				default: 24
			}
		},
		computed: {
			style() {
				return {
					'border-bottom-color': this.color,
					'display': this.isVisible === true ? 'inline-block' : 'none',
					'height': parseInt(this.size, 10) + 'px',
					'width': parseInt(this.size, 10) + 'px',
				};
			}
		}
	}
</script>

<style>
	@keyframes bluehost-spinner {
		0% {
			transform: translate3d(0, 0, 0) rotate(0deg);
		}
		100% {
			transform: translate3d(0, 0, 0) rotate(360deg);
		}
	}

	.bluehost-spinner {
		display: inline-block;
		margin-left: 16px;
		position: relative;
	}

	.bluehost-spinner::before {
		animation: 1.5s linear infinite bluehost-spinner;
		animation-play-state: inherit;
		border: solid 2px transparent;
		border-bottom-color: inherit;
		border-radius: 50%;
		content: "";
		position: absolute;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		transform: translate3d(0, 0, 0);
		will-change: transform;
	}
</style>
