<template>
	<div class="page --complete">

		<div class="content">

			<h1>Welcome to the Bluehost family!</h1>

			<p>
				You've transferred your website to Bluehost. Now we just need to get it set up on your Bluehost account
				so
				you can review it.
			</p>

			<a class="button"
			   href="https://my.bluehost.com/web-hosting/cplogin"
			   target="_blank"
			   rel="noreferrer noopener"
			>
				Login to Bluehost
			</a>

			<p>
				<span class="text-disabled">Don't have an account?</span>&nbsp;&nbsp;
				<a
					href="https://www.bluehost.com/cgi-bin/signup"
					target="_blank"
					rel="noreferrer noopener"
				>
					Create account
				</a>
			</p>

			<img v-bind:src="imageSrc"/>

		</div>

		<div class="footer"></div>

	</div>
</template>

<script>
	export default {
		data() {
			return {
				imageSrc: window.BHMove.pluginUrl + require('@/images/moving-truck-unloaded.svg').default
			}
		}
	}
</script>
