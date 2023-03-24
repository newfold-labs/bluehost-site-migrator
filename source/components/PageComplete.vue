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

      <h1>{{ __("Great news, your website has ", 'bluehost-site-migrator') }}</h1>
      <h1>{{ __("been cloned successfully! ", 'bluehost-site-migrator') }}</h1>

      <p>
        {{
          __("Your site has been cloned and is now ready for transfer. To initiate the transfer, you need to copy the transfer key and paste it into the Migration Services page.", 'bluehost-site-migrator')
        }}
      </p>

      <div class="migration-id-container">
        <h2>{{ migrationId }}</h2>
      </div>

      <a class="button"
         target="_blank"
         @click="copyText"
         rel="noreferrer noopener"
      >
        {{ __("Copy transfer key", 'bluehost-site-migrator') }}
      </a>

      <p>
        <a
            v-bind:href="signupUrl"
            target="_blank"
            rel="noreferrer noopener"
        >
          {{ __("Login to Bluehost", 'bluehost-site-migrator') }}
        </a>
      </p>

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
        {{ __("Choose your country:", 'bluehost-site-migrator') }}
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
      closeIcon: require('@/images/close.svg'),
      countryCode: window.BHSiteMigrator.countryCode,
      imageSrc: require('@/images/moving-truck-unloaded.svg'),
      loginUrl: '',
      message: '',
      showMessage: false,
      migrationId: null,
      options: [],
      regions: {},
      signupUrl: '',
    }
  },
  watch: {
    countryCode: function (countryCode) {
      this.setUrls(countryCode);
      if (this.showMessage) {
        this.message = `Country updated to ${ this.regions[countryCode].countryName }!`;
      }
    }
  },
  methods: {
    async copyText() {
      await navigator.clipboard.writeText(this.migrationId);
      this.message = "Copied transfer key to clipboard";
    },
    closeFlashMessage() {
      this.message = '';
    },
    getValidCountryCode(countryCode) {
      // If country code exists, use it
      if (this.regions.hasOwnProperty(countryCode)) {
        return countryCode;
      }
      // If country code doesn't exist, use an empty string (if valid)
      if (this.regions.hasOwnProperty('')) {
        return '';
      }
      // Otherwise, default to the first key
      return Object.keys(this.regions)[0];
    },
    setCountryCode(countryCode) {
      // This function is only called for initial update via API, so we don't want to show toast message.
      this.showMessage = false;
      // If country code is valid, use it
      this.countryCode = this.getValidCountryCode(countryCode);
      // Ensure toast message is enabled for all future updates to country code made by the user.
      this.showMessage = true;
    },
    setUrls(countryCode) {
      this.loginUrl = this.regions[this.getValidCountryCode(countryCode)].loginUrl;
      this.signupUrl = this.regions[this.getValidCountryCode(countryCode)].signupUrl;
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
            this.setCountryCode(countryCode);
            this.setUrls(countryCode);
          });
    },
  },
  mounted() {
    this.getMigrationData();
  }
}
</script>
