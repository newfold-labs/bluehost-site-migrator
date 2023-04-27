<template>
  <div class="page --check-compatibility">
    <div class="content">
      <h1>{{ __("Bluehost Site Migrator", 'bluehost-site-migrator') }}</h1>
      <p><strong>{{ __("Let's get this truck rolling:", 'bluehost-site-migrator') }}</strong></p>
      <p>{{ __("A website compatibility check needs to be performed before the", 'bluehost-site-migrator') }}</p>
      <p>{{ __("transfer process can begin to verify that your website can be transferred.", 'bluehost-site-migrator') }}</p>
      <p>{{ __("transferred.", 'bluehost-site-migrator') }}</p>
      <button v-on:click="checkCompatibility" v-bind:class="this.buttonClasses">
        {{ __("Check Compatibility", 'bluehost-site-migrator') }}
        <spinner color="white" v-bind:is-visible="this.isSpinnerVisible" size="18" />
      </button>
      <span class="message">{{ message }}</span>
    </div>
    <img v-bind:src="imageSrc" />
    <div class="footer"></div>
  </div>
</template>

<script>
import Spinner from "./Spinner.vue";
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

apiFetch.use(apiFetch.createNonceMiddleware(window.BHSiteMigrator.restNonce));
apiFetch.use(apiFetch.createRootURLMiddleware(window.BHSiteMigrator.restRootUrl));

export default {
  components: {Spinner},
  data() {
    return {
      geo: {},
      imageSrc: require('@/images/moving-truck-loaded.svg'),
      isButtonDisabled: false,
      isCompatible: null,
      isSpinnerVisible: false,
      message: ''
    }
  },
  computed: {
    buttonClasses() {
      return 'button' + (this.isButtonDisabled ? ' --is-disabled' : '');
    }
  },
  methods: {
    async geolocation() {
      await fetch('https://hiive.cloud/workers/geolocation/')
          .catch((error) => {
            console.error(error);
            this.$router.push('/error');
          })
          .then(response => response.json())
          .then(
              (data) => {
                this.geo = data;
              }
          );
    },
    async checkCompatibility() {
      this.isSpinnerVisible = true;
      this.isButtonDisabled = true;
      this.loopMessages();
      await this.geolocation();
      apiFetch(
          {
            path: '/bluehost-site-migrator/v1/can-we-migrate',
            method: 'POST',
            data: this.geo
          }
      )
          .catch((error) => {
            console.error(error);
            this.$router.push('/error');
            return {can_migrate: false}
          })
          .then(
              ({can_migrate}) => {
                this.isCompatible = can_migrate;
              }
          );
    },
    loopMessages() {
      const messages = [
        __('Checking environment...', 'bluehost-site-migrator'),
        __('Checking plugins...', 'bluehost-site-migrator'),
        __('Checking themes...', 'bluehost-site-migrator'),
        __('Checking configuration...', 'bluehost-site-migrator'),
      ];
      messages.forEach((msg, index) => {
        setTimeout(() => {
          this.message = msg;
        }, 5000 * index);
      });
    }
  },
  watch: {
    isCompatible(value) {
      this.$router.push(value ? '/compatible' : '/incompatible');
    }
  }
}
</script>
