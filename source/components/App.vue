<template>
	<div class="bluehost-move">
		<transition name="slide-fade">
			<router-view></router-view>
		</transition>
	</div>
</template>

<style>
	.slide-fade-enter-active {
		transition: all 1s ease;
	}

	.slide-fade-leave-active {
		transition: all 1s ease;
	}

	.slide-fade-enter, .slide-fade-leave-to {
		transform: translateX(-100vw);
		opacity: 0;
	}
</style>
