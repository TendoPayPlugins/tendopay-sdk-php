<template>
  <div class="card-body">
    <h6 class="card-title">Transactions</h6>
    <div class="card-text">
      <table class="table table-sm table-hover">
        <thead>
        <tr class="text-center">
          <th scope="col">#</th>
          <th scope="col">Status</th>
          <th scope="col">Message</th>
          <th scope="col">Detail</th>
          <th scope="col">Cancel</th>
        </tr>
        </thead>
        <tbody>
        <tr v-if="transactions" v-for="({transactionNumber, status, hash, message = ''}) in transactions" :key="hash"
            class="text-center">
          <th scope="row">{{ transactionNumber }}</th>
          <td>{{ status }}</td>
          <td>{{ message }}</td>
          <td>
            <button v-if="!loading.show.has(transactionNumber)" type="button" class="btn btn-sm btn-link"
                    @click="show(transactionNumber)">
              Show
            </button>
            <i class="fa fa-spinner fa-spin fa-fw" v-if="loading.show.has(transactionNumber)"></i>
          </td>
          <td>
            <button v-if="!loading.cancel.has(transactionNumber)" type="button" class="btn btn-sm btn-link"
                    @click="cancel(transactionNumber)">
              Cancel
            </button>
            <i class="fa fa-spinner fa-spin fa-fw" v-if="loading.cancel.has(transactionNumber)"></i>
          </td>
        </tr>
        <tr v-if="!transactions || transactions.length === 0" class="text-center">
          <td colspan="4">Transaction not found</td>
        </tr>
        </tbody>
      </table>

      <div class="alert alert-danger" role="alert" v-if="error">
        <span>{{ error }}</span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close" @click="error = null">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="collapse show" id="show-transaction" v-if="transaction">
        <div class="card card-body">
          <pre>{{ transaction }}</pre>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close" @click="transaction = null">
            <i class="fa fa-angle-double-up" aria-hidden="true"></i>
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
module.exports = {
  name: 'TpSdkTransactions',
  props: {
    value: {
      type: Array,
      default: () => null
    }

  },
  data () {
    return {
      transaction: null,
      transactions: this.value,
      loading: {
        show: new Set(),
        cancel: new Set(),
      },
      error: null,
    }
  },
  watch: {
    value: {
      deep: true,
      handler (val) {
        this.transactions = val
      }
    }
  },
  methods: {
    showError (e) {
      let message = null
      try {
        message = e.response.data.error
      } catch (e) {
        message = 'Unknown error'
      }
      this.error = message
      console.log(error)
    },
    show (transactionNumber) {
      this.transaction = null
      this.error = null
      this.loading.show.add(transactionNumber)
      this.loading.show = new Set(this.loading.show)
      axios.post('/api.php', {
        job: 'GET_TRANSACTION',
        transactionNumber,
      }).then(({ data }) => {
        // console.log('get transaction:', data)
        this.transaction = data
      }).catch(e => {
        this.showError(e)
      }).finally(() => {
        this.loading.show.delete(transactionNumber)
        this.loading.show = new Set(this.loading.show)
      })
    },
    cancel (transactionNumber) {
      this.transaction = null
      this.error = null
      this.loading.cancel.add(transactionNumber)
      this.loading.cancel = new Set(this.loading.cancel)
      axios.post('/api.php', {
        job: 'CANCEL_TRANSACTION',
        transactionNumber,
      }).then(({ data }) => {
        console.log('cancel transaction:', data)
        this.transactions = data
      }).catch(e => {
        this.showError(e)
      }).finally(() => {
        this.loading.cancel.delete(transactionNumber)
        this.loading.cancel = new Set(this.loading.cancel)
      })
    }
  }
}
</script>

<style scoped>

</style>
