<template>
  <div class="card">
    <div class="card-header d-flex" :id="id">
      <h5 class="mb-0 mr-auto">
        <button class="btn btn-link" data-toggle="collapse" :data-target="`#${collapseId}`" aria-expanded="true"
                :aria-controls="collapseId" @click="toggleContainer">
          <i class="fa" :class="{'fa-minus': expand, 'fa-plus': !expand}" aria-hidden="true"></i>
          {{ title }}
        </button>
      </h5>
      <slot name="side-header"></slot>
    </div>
    <div :id="collapseId" class="collapse show" v-if="expand" :aria-labelledby="id">
      <div class="card-body">
        <slot name="body"></slot>
      </div>
    </div>
  </div>
</template>

<script>
module.exports = {
  name: 'TpCollapse',
  props: {
    id: {
      type: String,
      required: true
    },
    title: {
      type: String,
      default: 'Click here'
    }
  },
  data () {
    return {
      expand: false
    }
  },
  computed: {
    collapseId() {
      return `collapse-${this.id}`
    }
  },
  methods: {
    toggleContainer () {
      this.expand = !this.expand
    }
  }
}
</script>

<style scoped>

</style>
