<template>
  <div class="w-screen py-24 lg:py-36 h-screen"  id="lists-list">
    <div class="w-3/4 m-auto">
      <h1 class="text-center text-blue-main">Our lists:</h1>
      <div 
      class="border-4 border-red-main rounded-lg w-full md:w-3/4 m-auto  h-64 my-3 overflow-hidden"
      v-for="list in lists" :key="list.id">
        <PostCard :post="list" />
      </div>
    </div>
  </div>
</template>

<script>
import PostCard from "@/components/PostCard.vue";

export default {
  components: { PostCard },
  computed: {
    lists() {
      return this.$store.getters.lists;
    },
  },
};
</script>

<style lang="scss">

</style>
