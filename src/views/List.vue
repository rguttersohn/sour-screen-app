<template>
  <div class="w-screen py-24 lg:py-36 h-screen">
    <div class="w-3/4 m-auto">
      <h1 class="text-center text-blue-main">Our lists:</h1>
      <div 
      class=""
      v-for="list in lists" :key="list.id">
        <ListCard :post="list" />
      </div>
    </div>
  </div>
</template>

<script>
import ListCard from "@/components/ListCard.vue";

export default {
  name:"Lists",
  components: { ListCard },
  computed: {
    lists() {
      return this.$store.getters.lists;
    },
  },
};
</script>

<style lang="scss">

</style>
