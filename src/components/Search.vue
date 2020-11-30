<template>
  <div class="search-wrapper">
    <form @change="searchPosts" @submit.prevent="">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search movies and lists"
      />
    </form>
    <div class="search-results" :class="{ 'search-active': isSearching }">
      <div @click="closeSearchContainer">
        <router-link
          @click.native="selectPost"
          v-for="result in searchResult"
          :key="result.id"
          :to="{ name: 'Post', params: { id: result.id } }"
          v-html="result.title.rendered"
        ></router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      searchQuery: "",
      isTyping: false,
      searchResult: [],
      isSearching: false,
    };
  },
  watch: {
    isTyping: function (value) {
      if (!value) {
        this.searchPosts(this.searchQuery);
        this.isSearching = true;
      }
    },
  },
  methods: {
    searchPosts: function () {
      this.isSearching = true;
      fetch(`http://3.89.20.61/wp-json/wp/v2/posts?search=${this.searchQuery}`)
        .then((resp) => resp.json())
        .then((result) => {
          this.searchResult = result;
        });
    },
    selectPost() {
      this.isSearching = false;
      this.searchQuery = "";
    },
    closeSearchContainer(event) {
      let searchContainerEvent = event;
      window.addEventListener("click", (event) => {
        if (event.target !== searchContainerEvent.target) {
          this.isSearching = false;
          this.searchQuery = "";
        }
      });
    },
  },
};
</script>

<style lang="scss">
$color-red: #ff3333;
$color-blue: #0099cc;
$color-lightred: #ffe7ff;
$color-lightblue: #b1bbed;

.search-wrapper {
  position: rleative;
  form {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    input {
      width: 100%;
      padding: 12px 20px;
      margin: 8px 0;
      display: inline-block;
      border: 3px solid $color-blue;
      border-radius: 15px;
      box-sizing: border-box;
    }
    input:focus{
        outline:none;
    }
    ::placeholder{
        color:$color-blue
    }
  }
  .search-results {
    max-height: 0;
    overflow: hidden;

    a {
      display: block;
    }
    a:hover {
      background-color: $color-lightred;
    }
  }
  .search-active {
    max-height: 100%;
    width: 400px;
    position: absolute;
    z-index: 1000;
    background-color: white;
    top: 75%;
    left: 50%;
    overflow-y: scroll;
    box-shadow:5px 5px 5px 2px $color-lightblue;
    border: 3px solid $color-lightblue;
  }
}
</style>