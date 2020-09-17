<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>TendoPay PHP SDK</title>
  <link rel="icon" type="image/png"
        href="https://s3.ca-central-1.amazonaws.com/candydigital/images/tendopay/tp-icon-32x32.png"/>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
        integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"
        integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
  <style>
      body {
          height: 100vh;
          font-size: 16px;
      }

      .container {
          height: 100%;
      }

      .card-img-top {
          width: 128px;
          height: 128px;
      }
  </style>
</head>
<body>
<div class="container d-flex justify-content-center">
  <div id="app">
    <div class="card mt-5" style="width: 800px;">

      <div class="d-flex justify-content-left align-items-center">
        <img class="card-img-top"
             src="https://s3.ca-central-1.amazonaws.com/candydigital/images/tendopay/tp-icon-128x128.png"
             alt="TendoPay Logo">
        <h2 class="h2 ml-3">TendoPay PHP SDK Tester</h2>
      </div>

      <div class="card-body">
        <p class="card-text">
          <tp-sdk-credentials v-model="credentials"/>
        </p>
        <p class="card-text">
          <tp-payment v-model="payload"/>
        </p>
      </div>

      <tp-sdk-transactions :value="transactions"/>

    </div>
  </div>
</div>
<!--<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.12/vue.min.js"></script>
<script src="https://unpkg.com/http-vue-loader"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.20.0/axios.min.js"
        integrity="sha512-quHCp3WbBNkwLfYUMd+KwBAgpVukJu5MncuQaWXgCrfgcxCJAq/fo+oqrRKOj+UKEmyMCG3tb8RB63W+EmrOBg=="
        crossorigin="anonymous"></script>
<script>
  const initCredentials = JSON.parse(JSON.stringify(<?php echo json_encode($initCredentials);?>))
  const API_END_POINT = '/api.php'

  new Vue({
    el: '#app',
    components: {
      TpSdkCredentials: window.httpVueLoader('/js/components/TpSdkCredentials.vue'),
      TpPayment: window.httpVueLoader('/js/components/TpPayment.vue'),
      TpSdkTransactions: window.httpVueLoader('/js/components/TpSdkTransactions.vue'),
    },
    data: {
      credentials: {
        merchant_id: null,
        merchant_secret: null,
        client_id: null,
        client_secret: null,
        redirect_url: 'http://localhost:8000/callback.php',
        error_redirect_url: 'http://localhost:8000/callback.php',
      },
      payload: {
        tp_description: null,
        tp_merchant_order_id: null,
        tp_amount: null
      },
      transactions: null,
    },
    methods: {
      async submit () {
        // console.log('submit', this.payload)
        const { data } = await axios.post(API_END_POINT, {
          job: 'GET_CREDENTIALS'
        })
        // console.log('getCredentials', data)
      },
      async initTransactions() {
        const { data } = await axios.post(API_END_POINT, {
          job: 'GET_TRANSACTIONS'
        })
        // console.log('getTransactions', data)
        this.transactions = data
      }
    },
    mounted () {
      this.credentials = {
        ...this.credentials,
        ...initCredentials,
      }
      this.initTransactions()
    }
  })
</script>
</body>
</html>
