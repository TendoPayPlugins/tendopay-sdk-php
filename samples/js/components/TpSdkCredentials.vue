<template>
  <tp-collapse id="tp-sdk-credentials" title="Credentials">
    <template #side-header>
      <div>
        <select class="form-control" v-model="credentials.tp_sdk_version" @change="saveCredentials">
          <option value="v2">Ver.2</option>
          <option value="v1">Ver.1</option>
        </select>
      </div>
    </template>
    <template #body>
      <div class="form-group" v-if="show.merchantCredentials">
        <label for="merchant_id">Merchant ID</label>
        <input type="text" class="form-control" id="merchant_id"
               v-model="credentials.merchant_id" @input="saveCredentials">
      </div>
      <div class="form-group" v-if="show.merchantCredentials">
        <label for="merchant_secret">Merchant Secret</label>
        <input type="text" class="form-control" id="merchant_secret"
               v-model="credentials.merchant_secret" @input="saveCredentials">
      </div>

      <div class="form-group">
        <label for="client_id">Client ID</label>
        <input type="text" class="form-control" id="client_id"
               v-model="credentials.client_id" @input="saveCredentials">
      </div>
      <div class="form-group">
        <label for="client_secret">Client Secret</label>
        <input type="text" class="form-control" id="client_secret"
               v-model="credentials.client_secret" @input="saveCredentials">
      </div>


      <div class="form-group">
        <label for="redirect_url">Redirect URL</label>
        <input type="text" class="form-control" id="redirect_url"
               v-model="credentials.redirect_url" @input="saveCredentials">
      </div>


      <div class="form-group" v-if="show.errorRedirectUrl">
        <label for="error_redirect_url">Error Redirect URL</label>
        <input type="text" class="form-control" id="error_redirect_url"
               v-model="credentials.error_redirect_url" @input="saveCredentials">
      </div>


    </template>
  </tp-collapse>
</template>

<script>
module.exports = {
  name: 'TpSdkCredentials',
  components: {
    TpCollapse: window.httpVueLoader('/js/components/TpCollapse.vue')
  },
  props: {
    value: {
      type: Object,
      default: () => {}
    }
  },
  data () {
    return {
      credentials: this.value,
    }
  },
  computed: {
    show () {
      const { tp_sdk_version = 'v2' } = this.credentials || {}

      if (tp_sdk_version === 'v2') {
        return {
          merchantCredentials: false,
          errorRedirectUrl: false,
        }
      }

      return {
        merchantCredentials: true,
        errorRedirectUrl: true,
      }
    }
  },
  methods: {
    async saveCredentials () {
      this.$emit('input', this.credentials)
      this.$emit('save', this.credentials)
    }
  },
}
</script>

<style scoped>

</style>
