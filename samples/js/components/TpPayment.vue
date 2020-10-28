<template>
  <tp-collapse id="tp-payment" title="Make Payment">
    <template #side-header>
      <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary" v-if="!loading" @click="submit">Checkout</button>
        <div class="mr-5" v-if="loading"><i class="fa fa-spinner fa-spin fa-fw"></i></div>
      </div>
    </template>
    <template #body>
      <form method="post" action="/payment.php" ref="form">
        <div class="form-group">
          <label for="tp_description">Brief Description</label>
          <input type="text" class="form-control" id="tp_description" name="tp_description"
                 v-model="payload.tp_description">
        </div>

        <div class="form-group">
          <label for="tp_merchant_order_id">Merchant Order ID</label>
          <input type="text" class="form-control" id="tp_merchant_order_id" name="tp_merchant_order_id"
                 v-model="payload.tp_merchant_order_id">
        </div>
        <div class="form-group">
          <label for="tp_amount">Subtotal Amount</label>
          <input type="text" class="form-control" id="tp_amount" name="tp_amount"
                 v-model="payload.tp_amount">
        </div>

<!--        <div class="d-flex justify-content-end">-->
<!--          <button type="button" class="btn btn-primary" v-if="!loading" @click="submit">Checkout</button>-->
<!--          <div class="mr-5" v-if="loading"><i class="fa fa-spinner fa-spin fa-fw"></i></div>-->
<!--        </div>-->
      </form>
    </template>
  </tp-collapse>
</template>

<script>
module.exports = {
  name: 'TpPayment',
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
      payload: this.value,
      loading: false,
    }
  },
  methods: {
    submit () {
      this.loading = true
      // this.$refs.form.submit()
      this.$emit('submit', this.payload)
    }
  },
  mounted () {
    this.loading = false
    // this.initOrder()
  }
}
</script>

<style scoped>

</style>
