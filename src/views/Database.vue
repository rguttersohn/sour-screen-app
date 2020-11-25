<template>
  <div>
    <h2>These are movies in our database</h2>
     <div v-for="post in posts" :key="post.id">
        <router-link :to="{name:'Post', params:{id:post.id}}">
          <p v-if="post.categories[0] === 4">{{post.title.rendered}}</p>
        </router-link>
      </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
    data(){
        return{
            posts:""
        }
    },
    computed:mapState({
        baseAPIURL:state=>state.baseAPIURL
    }),
    created:function(){
        fetch(`${this.baseAPIURL}/posts`)
            .then(resp=>resp.json()).then(posts =>
            this.posts = posts
            )
    }
};
</script>

<style lang="sass" scoped>
</style>