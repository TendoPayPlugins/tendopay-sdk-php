<template>
  <tp-collapse id="tp-sdk-credentials" title="Credentials">
    <template #body>
      <div class="form-group">
        <label for="merchant_id">Merchant ID</label>
        <input type="text" class="form-control" id="merchant_id"
               v-model="credentials.merchant_id" @input="saveCredentials">
      </div>
      <div class="form-group">
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


      <div class="form-group">
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
      credentials: this.value
    }
  },
  methods: {
    async saveCredentials () {
      try {
        window.setTimeout(async () => {
          const { data } = await axios.post(API_END_POINT, {
            job: 'SAVE_CREDENTIALS',
            credentials: this.credentials
          })
          // console.log('saveCredentials', data)
          this.$emit('input', this.credentials)
        }, 400)
      } catch (e) {
        console.log('error:saveCredentials() ', e)
      }
    }
  },
  mounted () {
    this.saveCredentials()
  }
}
</script>

<style scoped>

</style>
