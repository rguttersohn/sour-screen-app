<template>
  <router-link :to="{ name: 'Post', params: { id: post.id } }">
    <div class="post-card">
      <div>
        <img
          v-if="
            post._embedded['wp:featuredmedia'][0].media_details.sizes
              .medium_large !== undefined
          "
          :src="postImageML"
          alt=""
        />
        <img v-else :src="postImage" alt="" />
      </div>
      <div>
        <h2 v-html="post.title.rendered"></h2>
        <p v-html="post.excerpt.rendered"></p>
        <h3>read</h3>
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
    postImageML() {
      return this.post._embedded["wp:featuredmedia"][0].media_details.sizes
        .medium_large.source_url;
    },
    postImage() {
      return this.post._embedded["wp:featuredmedia"][0].media_details.sizes.full
        .source_url;
    },
  },
};
</script>

<style lang='scss'>
$color-red: #ff3333;
$color-blue: #0099cc;
$color-lightred: #ffe7ff;
$color-lightblue: #b1bbed;

.home-card-container {
  width:30%;
  background-color:white;
  border: 5px solid $color-blue;
      border-radius: 15px;
 
  a {
    text-decoration: none;
    .post-card {
      
      width: 100%;
      height: 25rem;
      margin: 3% 1%;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      transition: border-color 0.3s ease-in-out;

      &:hover,&:active,&:focus {
        border-color: $color-red;
      }

      div img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        object-position: 0 10%;
        transform: scale(1.1,1.1);
      }
      div:nth-child(2) {
        padding: 3%;
        background-color:white;
        height:100%;
        position:relative;
        
        h2,
        h3,
        p {
          margin:0 0 2%;
        }
        h2 {
          text-align: left;
          color:$color-red;
        }
        h3 {
          text-align: left;
          position:absolute;
          top:80%;
          left:3%;
          color:$color-blue;
        }
      }
    }
  }
}
</style>
