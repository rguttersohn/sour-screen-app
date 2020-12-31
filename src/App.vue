<template>
  <div id="app">
    <AppNav />
    <router-view :key="$route.fullPath" />
    <AppFooter />
  </div>
</template>

<script>
import AppNav from "@/components/AppNav.vue";
import AppFooter from '@/components/AppFooter.vue';
export default {
  components: {
    AppNav,
    AppFooter
  },
  created: function () {
    this.$store.dispatch("getPosts");
    this.$store.commit('GET_USER_INFO')
  },
  computed:{
    accessToken(){
      return this.$store.state.accessToken
    }
  }
};
</script>

<style lang="scss">
html{
  height:100%;
}
body{
  margin:0;
  min-height:100%;
  display:flex;
  flex-direction:column
}


</style>
