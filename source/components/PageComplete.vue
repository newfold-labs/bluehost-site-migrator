<template>
  <div class="page --complete">

    <div class="flash-message" v-if="message">
      <div class="flash-message__content">
        <div class="flash-message__type">Success</div>
        {{ message }}
      </div>
      <button class="flash-message__close" v-on:click="closeFlashMessage">
        <img v-bind:src="closeIcon" />
      </button>
    </div>

    <div class="content">

      <h1>{{ __("Welcome to the Bluehost family!", 'bluehost-site-migrator') }}</h1>

      <p>
        {{
          __("You've transferred your website to Bluehost. Now we just need to get it set up on your Bluehost account so you can review it.", 'bluehost-site-migrator')
        }}
      </p>

      <a class="button"
         v-bind:href="loginUrl"
         target="_blank"
         rel="noreferrer noopener"
      >
        {{ __("Login to Bluehost", 'bluehost-site-migrator') }}
      </a>

      <p>
        <span class="text-disabled">{{ __("Don't have an account?", 'bluehost-site-migrator') }}</span>&nbsp;&nbsp;
        <a
            v-bind:href="signupUrl"
            target="_blank"
            rel="noreferrer noopener"
        >
          {{ __("Create account", 'bluehost-site-migrator') }}
        </a>
      </p>

      <p class="text-disabled" v-if="Object.keys(regions).length > 1">
        {{ __("Wrong location? Choose your country:", 'bluehost-site-migrator') }}
        <select v-model="countryCode">
          <option v-for="option in options" v-bind:value="option.value">
            {{ option.text }}
          </option>
        </select>
      </p>

      <img class="main-image" v-bind:src="imageSrc" />

    </div>

    <div class="footer"></div>

  </div>
</template>

<script>
import apiFetch from '@wordpress/api-fetch';

apiFetch.use(apiFetch.createNonceMiddleware(window.BHSiteMigrator.restNonce));
apiFetch.use(apiFetch.createRootURLMiddleware(window.BHSiteMigrator.restRootUrl));

export default {
  data() {
    return {
      closeIcon: window.BHSiteMigrator.pluginUrl + require('@/images/close.svg').default,
      countryCode: 'US',
      imageSrc: window.BHSiteMigrator.pluginUrl + require('@/images/moving-truck-unloaded.svg').default,
      loginUrl: '',
      message: '',
      migrationId: null,
      options: [],
      regions: {},
      signupUrl: '',
    }
  },
  watch: {
    countryCode: function (countryCode) {
      this.loginUrl = this.regions[countryCode].loginUrl;
      this.signupUrl = this.regions[countryCode].signupUrl;
      this.message = `Country updated to ${ countryCode }!`;
    }
  },
  methods: {
    closeFlashMessage() {
      this.message = '';
    },
    getMigrationData() {
      apiFetch({path: '/bluehost-site-migrator/v1/migration-regions'})
          .catch((error) => {
            console.error(error);
            this.$router.push('/error');
          })
          .then(({countryCode, migrationId, regions}) => {
            this.migrationId = migrationId;
            this.signupUrl = `https://www.bluehost.com/web-hosting/signup?migrationId=${ migrationId }`;
            this.loginUrl = `https://my.bluehost.com/cgi/site_migration/?migrationId=${ migrationId }`;
            regions.forEach((region) => {
              const {countryCode, countryName} = region;
              this.regions[countryCode] = region;
              this.options.push({
                value: countryCode,
                text: countryName,
              });
            });
            this.countryCode = countryCode;
          });
    },
  },
  mounted() {
    this.getMigrationData();
  }
}
</script>
