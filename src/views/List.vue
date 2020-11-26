<template>
  <div>
    <h2>Our lists:</h2>
    <div v-for="list in lists" :key="list.id" class="post-card-container">
            <PostCard :post="list"/>
    </div>
  </div>
</template>

<script>
import PostCard from '@/components/PostCard.vue';

export default {
  components:{PostCard},
  computed: {
    lists(){
      return this.$store.getters.lists
    }
  }
};
</script>

<style lang="scss">

.post-card-container{

  a{
    text-decoration:none;
  }
}

</style>
