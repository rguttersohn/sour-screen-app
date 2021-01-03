<template>
  <div class="w-screen py-24 lg:py-36 h-full" id="user-page">
    <div class="w-full md:w-11/12 lg:w-3/4 m-auto my-10">
      <h1 class="text-red-main">Oh Hi, {{ userName }}</h1>
    </div>
    <section
    id="suggested-no-1">
      <div>
        <h2 class="text-blue-main my-10">Because you liked "{{matchedPosts[0].title.rendered}}"</h2>
        <div class="post-card-container m-auto">
          <div
            class="flex-shrink-0"
            v-for="post in relatedToLikes[0]"
            :key="post.id"
          >
            <PostCard :post="post" />
          </div>
        </div>
      </div>
    </section>
    <section id="suggested-no-2">
            <div>
        <h2 class="text-blue-main my-10">Because you liked "{{matchedPosts[1].title.rendered}}"</h2>
        <div class="post-card-container m-auto">
          <div
            class="flex-shrink-0"
            v-for="post in relatedToLikes[1]"
            :key="post.id"
          >
            <PostCard :post="post" />
          </div>
        </div>
      </div>
    </section>
    <section id="suggested-no-3">
            <div>
        <h2 class="text-blue-main my-10">Because you liked "{{matchedPosts[2].title.rendered}}"</h2>
        <div class="post-card-container m-auto">
          <div
            class="flex-shrink-0"
            v-for="post in relatedToLikes[2]"
            :key="post.id"
          >
            <PostCard :post="post" />
          </div>
        </div>
      </div>
    </section>
    <section id="user-likes">
      <div>
        <h1 class="text-center text-red-main my-10">Here are your likes:</h1>
        <div class="post-card-container m-auto">
          <p v-if="userLikes.length === 0">
            Click the like button on the individual movie pages to add to your
            list list
          </p>
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
    </section>
  </div>
</template>

<script>
import PostCard from "@/components/PostCard.vue";
import { mapState } from "vuex";
export default {
  components: { PostCard },
  data() {
    return {
      matchedPosts: [],
      relatedToLikes: [{}, {}, {}],
    };
  },
  computed: mapState({
    userName: (state) => state.userInfo.username,
    userLikes: (state) => state.userInfo.likes.reverse(),
    posts: (state) => state.posts,
  }),
  created() {
    this.$store.dispatch("getUserLikes");
  },
  methods: {
    matchUserLikes() {
      if (this.userLikes.length !== 0) {
        for (let i = 0; i < this.userLikes.length; i++) {
          this.posts.forEach((post) => {
            if (this.userLikes[i].post_id === post.id) {
              this.matchedPosts.push(post);
            }
          });
        }
      }
    },
    createRelatedToLike(relatedPost, indexValue) {
      let postsCopy = this.posts.slice();
      postsCopy.forEach((post) => {
        post.relatedScore = 0;
        if (post.id !== relatedPost[indexValue].id) {
          post._embedded["wp:term"][0].forEach((el) => {
            for (
              let i = 0;
              i < relatedPost[indexValue]._embedded["wp:term"][0].length;
              i++
            ) {
              if (
                el.name ===
                relatedPost[indexValue]._embedded["wp:term"][0][i].name
              ) {
                post.relatedScore = post.relatedScore + 3;
              }
            }
          });
          post._embedded["wp:term"][1].forEach((el) => {
            for (
              let i = 0;
              i < relatedPost[indexValue]._embedded["wp:term"][1].length;
              i++
            ) {
              if (
                el.name ===
                relatedPost[indexValue]._embedded["wp:term"][1][i].name
              ) {
                post.relatedScore = post.relatedScore + 1;
              }
            }
          });
        }
      });
      this.relatedToLikes[indexValue] = postsCopy
        .sort((a, b) => a.relatedScore - b.relatedScore)
        .reverse()
        .slice(0, 10);
    },
  },
  watch: {
    posts() {
      this.matchUserLikes();
    },
    userLikes() {
      this.matchUserLikes();
    },
    matchedPosts() {
      if (this.matchedPosts[0]){
      this.createRelatedToLike(this.matchedPosts, 0);
      }
      if (this.matchedPosts[1]){
      this.createRelatedToLike(this.matchedPosts, 1);
      }
      if (this.matchedPosts[1]){
      this.createRelatedToLike(this.matchedPosts, 2);
      }
    },
  },
};
</script>

<style>
#user-page .post-card-container {
  @apply bg-red-xLight w-full flex flex-row items-center overflow-x-scroll shadow-inner h-80 m-auto w-11/12 px-5;
}

#user-page section {
  @apply my-20 w-full;
}
</style>