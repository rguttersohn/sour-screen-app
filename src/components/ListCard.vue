<template>
  <router-link :to="{ name: 'Post', params: { id: post.id } }">
    <div class="list-card h-40 bg-cover bg-center relative">
      <div
        class=" w-56 h-full  bg-cover bg-top"
        :style="{ backgroundImage: `url(${postImage}` }"
      ></div>
      <div class="p-5">
        <h2 v-html="post.title.rendered"></h2>
        <p v-html="post.excerpt.rendered"></p>
      </div>
    </div>
  </router-link>
</template>

<script>
export default {
  props: {
    post: Object,
  },
  computed: {
    postImage() {
      return this.post.jetpack_featured_media_url;
    },
    starterIcon() {
      return `${this.$store.state.baseHostURL}wp-content/uploads/2020/12/favorite-icon.svg`;
    },
  },
};
</script>

<style>
.list-card {
  width: 250px;
}

.list-card {
  @apply mx-2;
  @apply h-60;
  @apply border;
}
</style>