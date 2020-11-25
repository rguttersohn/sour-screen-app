<template>
  <div>
    <h2>Our lists:</h2>
    <router-link to="/post">
      <div v-for="post in posts" :key="post.id">
          <p v-if="post.categories[0] === 3">{{post.title.rendered}}</p>
      </div>
    </router-link>
  </div>
</template>

<script>
import {mapState} from 'vuex'
  export default {
    data(){
      return{
        posts:""
      }
    },
    computed:mapState({
      baseAPIURL: state=>state.baseAPIURL
    }),
    created:function(){
    fetch(`${this.baseAPIURL}/posts`).then(resp=>resp.json()).then(posts=>
    this.posts = posts)
  }
  }
  
</script>

<style lang="sass" scoped>

</style>
