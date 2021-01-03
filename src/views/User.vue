<template>
  <div
  class="w-screen py-24 lg:py-36 h-full" id="user-page">
    <div class="w-full md:w-11/12 lg:w-3/4 m-auto my-10">
      <h1
      class="text-red-main"
      >Oh Hi, {{ userName }}</h1>
    </div>
    <div>
        <h1
        class="text-center text-red-main my-10"
        >Here are your likes:</h1>
        <div class="post-card-container m-auto">
            <p v-if="userLikes.length === 0">Click the like button on the individual movie pages to add to your list list</p>
            <div
            v-else
                class="flex-shrink-0"
                v-for="post in matchedPosts"
                :key="post.id"
              >
                <PostCard :post="post" />
              </div>
        </div>
    </div>
  </div>
</template>

<script>
import PostCard from '@/components/PostCard.vue';
import {mapState} from 'vuex'
export default {
components:{PostCard},
data(){
    return{
        matchedPosts:[],
    }
},
  computed:mapState({
    userName:state=>state.userInfo.username,
    userLikes:state=>state.userInfo.likes.reverse(),
    posts:state=>state.posts
  }),
  created() {
    this.$store.dispatch("getUserLikes");
  },
  methods:{
      matchUserLikes(){
          if (this.userLikes.length !== 0){
              for (let i = 0;i<this.userLikes.length;i++){
                  this.posts.forEach(post=>{
                      if (this.userLikes[i].post_id === post.id){
                          this.matchedPosts.push(post)
                      }
                  })
              }
          }
          
      }
  },
  watch:{
      posts(){
          this.matchUserLikes()
      },
      userLikes(){
          this.matchUserLikes()
      }
  }
};
</script>

<style>

#user-page .post-card-container {
  @apply bg-red-xLight w-full flex flex-row items-center overflow-x-scroll shadow-inner h-80 m-auto w-11/12 px-5;
}

#user-page section{
  @apply my-20 w-full;
}
</style>