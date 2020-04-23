<template>
	<div class="page --complete">

		<div class="content">

			<h1>{{__("Welcome to the Bluehost family!", 'bluehost-site-migrator')}}</h1>

			<p>
				{{__("You've transferred your website to Bluehost. Now we just need to get it set up on your Bluehost account so you can review it.", 'bluehost-site-migrator')}}
			</p>

			<a class="button"
			   href="https://my.bluehost.com/web-hosting/cplogin"
			   target="_blank"
			   rel="noreferrer noopener"
			>
				{{__("Login to Bluehost", 'bluehost-site-migrator')}}
			</a>

			<p>
				<span class="text-disabled">{{__("Don't have an account?", 'bluehost-site-migrator')}}</span>&nbsp;&nbsp;
				<a
					href="https://www.bluehost.com/cgi-bin/signup"
					target="_blank"
					rel="noreferrer noopener"
				>
					{{__("Create account", 'bluehost-site-migrator')}}
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
				imageSrc: window.BHSiteMigrator.pluginUrl + require('@/images/moving-truck-unloaded.svg').default
			}
		}
	}
</script>
