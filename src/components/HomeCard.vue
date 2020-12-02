<template>
  <router-link
    @mouseover.native="mousedOver = true"
    @mouseleave.native="mousedOver = false"
    :to="{ name: 'Post', params: { id: post.id } }"
  >
    <div class="post-card">
      <div>
        <img
          :class="{ 'animate-homecard-image': mousedOver }"
          v-if="
            post._embedded['wp:featuredmedia'][0].media_details.sizes
              .medium_large !== undefined
          "
          :src="postImageML"
          alt=""
        />
        <img
          :class="{ 'animate-homecard-image': mousedOver }"
          v-else
          :src="postImage"
          alt=""
        />
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
  data() {
    return {
      mousedOver: false,
    };
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
  width: 30%;
  background-color: white;
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

      &:hover,
      &:active,
      &:focus {
        border-color: $color-red;
      }

      div img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        object-position: 0 10%;
        transform: scale(1.1, 1.1);
      }

      div img.animate-homecard-image {
        animation: flicker, warp;
        animation-duration: 3s;
        animation-fill-mode: forwards;
        animation-iteration-count: infinite;
      }

      svg.play-button {
        stroke-width: 2px;
        fill: $color-blue;
      }

      div:nth-child(2) {
        padding: 3%;
        background-color: white;
        height: 100%;
        position: relative;

        h2,
        h3,
        p {
          margin: 0 0 2%;
        }
        h2 {
          text-align: left;
          color: $color-red;
        }
        h3 {
          text-align: left;
          position: absolute;
          top: 80%;
          left: 3%;
          color: $color-blue;
        }
      }
    }
  }
}

@keyframes flicker {
  0% {
    opacity: 1;
  }

  33% {
    opacity: 1;
  }

  34% {
    opacity: 0;
  }

  35% {
    opacity: 1;
  }

  75% {
    opacity: 1;
  }

  76% {
    opacity: 0;
  }

  77% {
    opacity: 1;
  }

  80% {
    opacity: 1;
  }

  81% {
    opacity: 0;
  }

  82% {
    opacity: 1;
  }
}

@keyframes warp {
  0% {
    transform: scale(1.1, 1.1);
  }
  10% {
    transform: scale(1.1, 1.1);
  }
  11% {
    transform: scale(3.5, 1.1);
  }
  17% {
    transform: scale(3.5, 1.1);
  }
  18% {
    transform: scale(1.1, 1.1);
  }

  55% {
    transform: scale(1.1, 1.1);
  }

  56% {
    transform: scale(3.5, 2);
  }

  58% {
    transform: scale(1.1, 1.1);
  }
  100% {
    transform: scale(1.1, 1.1);
  }
}
</style>
