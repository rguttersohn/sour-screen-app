<template>
  <div id="app">
    <AppNav />
    <router-view :key="$route.fullPath" />
  </div>
</template>

<script>
import AppNav from "@/components/AppNav.vue";
export default {
  components: {
    AppNav,
  },
  created: function () {
    this.$store.dispatch("getPosts");
  },
};
</script>

<style lang="scss">
$color-red: #ff3333;
$color-blue: #0099cc;
$color-lightred: #ffe7ff;
$color-lightblue: #b1bbed;

body {
  width: 100vw;
  height: 100vh;
  margin: auto;
}

</style>
