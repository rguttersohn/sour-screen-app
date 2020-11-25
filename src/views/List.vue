<template>
  <div>
    <h2>Our lists:</h2>
    <div v-for="list in lists" :key="list.id" class="post-card-container">
      <router-link :to="{name:'Post',params:{id:list.id}}" >
            <PostCard :post="list"/>
      </router-link>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";
import PostCard from '@/components/PostCard.vue';

export default {
  components:{PostCard},
  computed: mapState({
    lists:state=>state.lists
  })
};
</script>

<style lang="scss">

.post-card-container{

  a{
    text-decoration:none;
  }
}

</style>
