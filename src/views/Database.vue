<template>
  <div>
    <h2>These are movies in our database</h2>
    <ul v-for="post in posts" :key="post.id">
        <li v-if="post.categories[0] === 2">{{post.title.rendered}}</li>
    </ul>
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
        baseURL:state=>state.baseURL
    }),
    created:function(){
        fetch(`${this.baseURL}/posts`)
            .then(resp=>resp.json()).then(posts =>
            this.posts = posts
            )
    }
};
</script>

<style lang="sass" scoped>
</style>