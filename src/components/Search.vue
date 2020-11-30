<template>
  <div class="search-wrapper">
    <form @change="searchPosts" @submit.prevent="">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Example: The Room"
      />
      <!-- <input name="Submit" type="submit" value="submit"> -->
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="feather feather-search"
      >
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
    </form>
    <div class="search-results" :class="{ 'search-active': isSearching }">
      <div>
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
    width: 300px;
input, select{
    width: 100%;
    padding: 12px 20px;
    margin: 8px 0;
    display:inline-block;
    border: 1px solid $color-blue;
    border-radius: 15px;
    box-sizing: border-box;
}

    svg {
      stroke: lightgray;
      width: 50%;
      transition: stroke 0.3s ease-in-out;
      cursor:pointer;
    }

    &:hover > svg {
      stroke: $color-red;
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
    height: 500px;
    max-height: 100%;
    width: 400px;
    position: absolute;
    z-index: 1000;
    background-color: white;
    top: 75%;
    left: 50%;
    overflow-y: scroll;
  }
}
</style>