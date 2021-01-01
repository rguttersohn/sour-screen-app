<template>
  <div>
    <div class="w-screen lg:pt-36 pt-24">
      <h2>Hi, {{ userName }}</h2>
    </div>
    <div>
        <h3>Here are your likes</h3>
        <ul v-for="(like,index) in userLikes" :key="index">
           <li v-html="like.post_title"></li>
        </ul>
    </div>
  </div>
</template>

<script>
export default {
  computed: {
    userName() {
      return this.$store.state.userInfo.username;
    },
    userLikes() {
      return this.$store.state.userInfo.likes;
    },
  },
  created() {
    console.log('created')
    this.$store.dispatch("getUserLikes");
  },
};
</script>

<style scoped>
</style>