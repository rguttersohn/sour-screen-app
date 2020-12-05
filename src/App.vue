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

h1 {
  font-family: "Oswald", Arial, sans-serif;
  font-size: 48px;
  font-style: normal;
  font-variant: normal;
  font-weight: bolder;
  line-height: 26.4px;
  color: $color-red;
  text-transform: uppercase;
  line-height: 120%;
}

h2 {
  font-family: Oswald;
  font-size: 24px;
  font-style: normal;
  font-variant: normal;
  font-weight: bold;
  line-height: 120%;
  text-transform: uppercase;
}

h3 {
  font-family: Georgia, "Times New Roman", Times, serif;
  font-size: 18px;
  font-style: normal;
  font-variant: normal;
  font-weight: 700;
  line-height: 15.4px;
  line-height: 120%;
}
p {
  font-family: Georgia, "Times New Roman", Times, serif;
  font-size: 16px;
  font-style: normal;
  font-variant: normal;
  font-weight: 400;
  line-height: 20px;
  line-height: 120%;
}
blockquote {
  font-family: Oswald;
  font-size: 21px;
  font-style: normal;
  font-variant: normal;
  font-weight: 400;
  line-height: 30px;
}
pre {
  font-family: Oswald;
  font-size: 13px;
  font-style: normal;
  font-variant: normal;
  font-weight: 400;
  line-height: 18.5667px;
}
a {
  font-weight: 700;
  color: #2c3e50;
}
</style>
