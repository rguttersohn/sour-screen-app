<template>
  <div id="app">
    <div id="nav">
      <router-link to="/">
        <img :src="logoURL" alt="" />
      </router-link>
      <router-link to="/database">Database</router-link>
      <router-link to="/list">List</router-link>
    </div>
    <router-view />
  </div>
</template>

<script>
import { mapState } from "vuex";
export default {
  computed: {
    logoURL(){
      return `${this.baseHostURL}/wp-content/uploads/2020/11/sour_screen_logo-animated.svg`
    },
    ...mapState({
      baseHostURL: (state) => state.baseHostURL,
    }),
  },
};
</script>

<style lang="scss">
#app {
  font-family: Oswald, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  width: 100vw;
  height: 100vh;

  #nav {
    padding: 30px;
    display: flex;
    justify-content: space-evenly;
    align-items:center;
    border-bottom: 3px solid lightgray;
    position: sticky;
    top:0px;
    z-index:999;
    width:75%;
    height:50px;
    margin:auto;
    background-color:white;
    
    a {
      font-weight: 700;
      color: #2c3e50;
      text-decoration:none;
    }

    a.router-link-exact-active {
      color: #42b983;
    }
  }
}

h1 {
  font-family: Oswald;
  font-size: 24px;
  font-style: normal;
  font-variant: normal;
  font-weight: 700;
  line-height: 26.4px;
}
h3 {
  font-family: Oswald;
  font-size: 14px;
  font-style: normal;
  font-variant: normal;
  font-weight: 700;
  line-height: 15.4px;
}
p {
  font-family: Oswald;
  font-size: 14px;
  font-style: normal;
  font-variant: normal;
  font-weight: 400;
  line-height: 20px;
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
