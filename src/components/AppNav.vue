<template>
  <div id="nav" class="h-36 w-screen flex justify-center items-center">
    <div class="w-9/12 my-3 h-3/12 flex justify-between items-center">
      <div class="logo-container">
        <router-link to="/">
          <img :src="logoURL" alt="sour screen logo" />
        </router-link>
      </div>
      <div class="w-8/12 flex justify-evenly items-center">
        <router-link to="/database"><h2>Database</h2></router-link>
        <router-link to="/list"><h2>List</h2></router-link>
        <Search />
      </div>
    </div>
  </div>
</template>

<script>
import Search from "@/components/Search.vue";
export default {
  components: {
    Search,
  },
  computed: {
    logoURL() {
      return `${this.$store.state.baseHostURL}/wp-content/uploads/2020/12/sour_screen_logo-animated-v2.svg`;
    },
  },
};
</script>

<style lang="scss">
$color-red: #ff3333;
$color-blue: #0099cc;
$color-lightred: #ffe7ff;
$color-lightblue: #b1bbed;
</style>