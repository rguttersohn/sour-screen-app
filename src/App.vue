<template>
  <div id="app">
    <div id="nav">
      <div class="nav-container">
        <router-link to="/">
          <img :src="logoURL" alt="" />
        </router-link>
        <router-link to="/database"><h2>
          Database
        </h2></router-link>
        <router-link to="/list"><h2>
          List
        </h2></router-link>
      </div>
    </div>
    <router-view />
  </div>
</template>

<script>
import { mapState } from "vuex";
export default {
  computed: {
    logoURL() {
      return `${this.baseHostURL}/wp-content/uploads/2020/11/sour_screen_logo-animated.svg`;
    },
    ...mapState({
      baseHostURL: (state) => state.baseHostURL,
    }),
  },
};
</script>

<style lang="scss">
$color-red: #ff3333;
$color-blue: #0099cc;
$color-lightred: #ffe7ff;
$color-lightblue: #b1bbed;

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
    width:100vw;
    border-bottom: 3px solid $color-lightblue;
    position: sticky;
    top: 0px;
    z-index: 999;
    background-color: white;

    .nav-container {
      display: flex;
      justify-content: space-evenly;
      align-items: center;
      width: 75%;
      height: 50px;
      margin: auto;

      a {
        font-weight: 700;
        color: #2c3e50;
        text-decoration: none;

        h2{
          text-transform:uppercase;
        }
      }

      a.router-link-exact-active {
        color:$color-blue;
        border-bottom: 2px solid $color-red;
        padding-bottom: 0;
      }
    }
  }
}

h1 {
  font-family: Oswald;
  font-size: 36px;
  font-style: normal;
  font-variant: normal;
  font-weight: 700;
  line-height: 26.4px;
  color:$color-red;
  text-transform:uppercase;
}

h2{

  font-family: Oswald;
  font-size: 24px;
  font-style: normal;
  font-variant: normal;
  font-weight: 700;
  line-height: 26.4px;
}

h3 {
  font-family: Georgia, 'Times New Roman', Times, serif;
  font-size: 18px;
  font-style: normal;
  font-variant: normal;
  font-weight: 700;
  line-height: 15.4px;
}
p {
  font-family: Georgia, 'Times New Roman', Times, serif;
  font-size: 16px;
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
