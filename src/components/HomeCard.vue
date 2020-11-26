<template>
  <router-link :to="{ name: 'Post', params: { id: post.id } }">
    <div class="post-card">
      <div>
        <img :src="image" alt="" />
      </div>
      <div>
        <h2 v-html="post.title.rendered"></h2>
        <p v-html="post.excerpt.rendered"></p>
      </div>
    </div>
  </router-link>
</template>

<script>
import { mapState } from "vuex";
export default {
  props: {
    post: Object,
  },
  data() {
    return {
      image: "",
    };
  },
  computed: mapState({
    baseAPIURL: (state) => state.baseAPIURL,
  }),
  created() {
    fetch(`${this.baseAPIURL}/posts/${this.post.id}?_embed`)
      .then((resp) => resp.json())
      .then((img) => {
        if (img._embedded["wp:featuredmedia"]) {
          this.image = img._embedded["wp:featuredmedia"][0].source_url;
        }
      });
  },
};
</script>

<style lang='scss'>
$color-red: #ff3333;
$color-blue: #0099cc;
$color-lightred: #ffe7ff;
$color-lightblue: #b1bbed;

.home-card-container {
  .post-card {
    border: 5px solid $color-lightblue;
    border-radius: 15px;
    width: 400px;
    height: 400px;
    margin: 3% 1%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: border-color 0.3s ease-in-out;

    a {
      text-decoration: none;
    }

    &:hover {
      border-color: $color-lightred;
    }

    div img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      object-position: 0 10%;
    }
    div {
      h2,
      p {
        text-decoration: none;
      }
    }
  }
}
</style>
